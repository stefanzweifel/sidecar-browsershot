const assert = require('node:assert/strict');
const { spawnSync } = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { test } = require('node:test');

for (const scenario of ['success', 'base64', 'render failure', 'upload failure', 'cleanup failure', 'cleanup warning', 'directory failure', 'keep crash dumps', 'warming']) {
    test(`cleans invocation files safely: ${scenario}`, () => {
        const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'sidecar-cleanup-test-'));

        try {
            fs.copyFileSync(
                path.join(__dirname, '../../resources/lambda/browsershot.js'),
                path.join(directory, 'browsershot.cjs'),
            );

            const chromiumDirectory = path.join(directory, 'node_modules/@sparticuz/chromium');
            fs.mkdirSync(chromiumDirectory, { recursive: true });
            fs.writeFileSync(path.join(chromiumDirectory, 'index.js'), `
                module.exports = {
                    args: [],
                    executablePath: async () => '/tmp/chromium',
                };
            `);

            const result = spawnSync(process.execPath, ['-e', `
                const assert = require('node:assert/strict');
                const Module = require('node:module');
                const originalLoad = Module._load;
                const scenario = ${JSON.stringify(scenario)};
                const failure = new Error('render failed');
                const warnings = [];
                console.warn = (...args) => warnings.push(args);
                const files = new Map([
                    ['/tmp/chromium', 'cached executable'],
                    ['/tmp/fonts', 'cached fonts'],
                    ['/tmp/unrelated', 'other data'],
                    ['/tmp/puppeteer_dev_chrome_profile-stale', 'old profile'],
                    ['/tmp/index.html', 'old HTML'],
                    ['/tmp/browsershot.js', 'old options'],
                    ['/tmp/browsershot', 'old output'],
                    ['/tmp/core.chromium.123', 'old crash dump'],
                ]);
                const keepCrashDumps = scenario === 'keep crash dumps';
                let command;
                let executed = false;
                let uploaded = false;
                let removals = 0;
                Module._load = function (name, ...parameters) {
                    if (name === 'fs') return {
                        readdirSync: () => {
                            if (scenario === 'directory failure') throw new Error('cannot list');
                            return [...files.keys()].map(file => file.slice('/tmp/'.length));
                        },
                        writeFileSync: (file, contents) => files.set(file, contents),
                        readFileSync: file => files.get(file),
                        unlinkSync: file => files.delete(file),
                        rmSync: (file, options) => {
                            removals++;
                            assert.equal(options.force, true);
                            if (['cleanup failure', 'cleanup warning'].includes(scenario) && file.endsWith('profile-stale')) {
                                throw new Error('cannot remove profile');
                            }
                            files.delete(file);
                        },
                    };
                    if (name === 'child_process') return {
                        execSync: (cmd) => {
                            executed = true;
                            command = cmd;
                            if (!['cleanup failure', 'cleanup warning', 'directory failure'].includes(scenario)) {
                                assert.equal(files.has('/tmp/puppeteer_dev_chrome_profile-stale'), false);
                                assert.equal(files.has('/tmp/browsershot'), false);
                                assert.equal(files.has('/tmp/core.chromium.123'), keepCrashDumps);
                            }
                            files.set('/tmp/puppeteer_dev_chrome_profile-current', 'new profile');
                            files.set('/tmp/core.chromium.456', 'new crash dump');
                            files.set('/tmp/browsershot', Buffer.from('rendered PDF'));
                            if (['render failure', 'cleanup failure', 'directory failure'].includes(scenario)) {
                                throw failure;
                            }
                            return Buffer.from('rendered');
                        },
                    };
                    if (name === '@aws-sdk/client-s3') return { S3: class {
                        async putObject(params) {
                            assert.equal(files.has('/tmp/index.html'), true);
                            assert.equal(params.Body.toString(), 'rendered PDF');
                            if (scenario === 'upload failure') throw failure;
                            uploaded = true;
                            return { ETag: 'uploaded' };
                        }
                    } };
                    return originalLoad.call(this, name, ...parameters);
                };

                (async () => {
                    const { handle } = require('./browsershot.cjs');
                    if (scenario === 'warming') {
                        assert.equal(await handle({ warming: true }), undefined);
                        assert.equal(executed, false);
                        assert.equal(removals, 0);
                        return;
                    }
                    const event = { _html: '<p>Hello</p>', options: { path: 'output.pdf' } };
                    if (scenario !== 'base64') event.options.s3 = { bucket: 'bucket', path: 'output.pdf' };
                    if (keepCrashDumps) event._keepCrashDumps = true;
                    if (scenario.endsWith('failure')) {
                        await assert.rejects(handle(event), error => error === failure);
                    } else {
                        assert.equal(await handle(event), scenario === 'base64'
                            ? Buffer.from('rendered PDF').toString('base64')
                            : 'uploaded');
                        assert.equal(uploaded, scenario !== 'base64');
                    }
                    assert.equal(executed, true);
                    assert.equal(command.startsWith('ulimit -c 0 && node ./browser.cjs '), !keepCrashDumps);
                    if (scenario !== 'directory failure') {
                        assert.equal(files.has('/tmp/index.html'), false);
                        assert.equal(files.has('/tmp/browsershot.js'), false);
                        assert.equal(files.has('/tmp/browsershot'), false);
                        assert.equal(files.has('/tmp/puppeteer_dev_chrome_profile-current'), false);
                        assert.equal(files.has('/tmp/core.chromium.123'), keepCrashDumps);
                        assert.equal(files.has('/tmp/core.chromium.456'), keepCrashDumps);
                    }
                    assert.equal(files.get('/tmp/chromium'), 'cached executable');
                    assert.equal(files.get('/tmp/fonts'), 'cached fonts');
                    assert.equal(files.get('/tmp/unrelated'), 'other data');
                    assert.equal(warnings.length > 0, ['cleanup failure', 'cleanup warning', 'directory failure'].includes(scenario));
                })().catch(error => { console.error(error); process.exitCode = 1; });
            `], { cwd: directory, encoding: 'utf8' });

            assert.equal(result.status, 0, result.stderr || result.stdout);
        } finally {
            fs.rmSync(directory, { recursive: true, force: true });
        }
    });
}
