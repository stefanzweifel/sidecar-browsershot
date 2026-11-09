const assert = require('node:assert/strict');
const { spawnSync } = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { test } = require('node:test');

for (const moduleType of ['module', 'commonjs']) {
    test(`loads ${moduleType} Chromium with Lambda's require(ESM) restriction`, () => {
        const directory = fs.mkdtempSync(path.join(os.tmpdir(), 'sidecar-module-test-'));

        try {
            fs.copyFileSync(
                path.join(__dirname, '../../resources/lambda/browsershot.js'),
                path.join(directory, 'browsershot.cjs'),
            );

            const chromiumDirectory = path.join(directory, 'node_modules/@sparticuz/chromium');
            fs.mkdirSync(chromiumDirectory, { recursive: true });
            fs.writeFileSync(path.join(chromiumDirectory, 'package.json'), JSON.stringify({
                type: moduleType,
                main: 'index.js',
            }));
            fs.writeFileSync(path.join(chromiumDirectory, 'index.js'), `
                ${moduleType === 'module' ? 'export default' : 'module.exports ='} {
                    args: ['--chromium-argument'],
                    executablePath: async () => '/test/chromium',
                };
            `);

            const result = spawnSync(process.execPath, [
                '--no-experimental-require-module',
                '-e',
                `
                    const assert = require('node:assert/strict');
                    const Module = require('node:module');
                    const originalLoad = Module._load;
                    let options;

                    Module._load = function (name, ...parameters) {
                        if (name === 'fs') {
                            return {
                                writeFileSync: (file, contents) => { options = JSON.parse(contents); },
                                readdirSync: () => [],
                            };
                        }
                        if (name === 'child_process') {
                            return { execSync: () => Buffer.from('rendered') };
                        }
                        if (name === '@aws-sdk/client-s3') {
                            return { S3: class {} };
                        }
                        return originalLoad.call(this, name, ...parameters);
                    };

                    (async () => {
                        const { handle } = require('./browsershot.cjs');
                        assert.equal(await handle({ warming: true }), undefined);
                        assert.equal(await handle({ options: { args: ['--custom'] } }), 'rendered');
                        assert.equal(options.options.executablePath, '/test/chromium');
                        assert.deepEqual(options.options.args, ['--custom', '--chromium-argument']);
                    })().catch(error => { console.error(error); process.exitCode = 1; });
                `,
            ], { cwd: directory, encoding: 'utf8' });

            assert.equal(result.status, 0, result.stderr || result.stdout);
        } finally {
            fs.rmSync(directory, { recursive: true, force: true });
        }
    });
}
