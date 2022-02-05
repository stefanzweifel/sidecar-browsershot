const fs = require('fs');
const {execSync} = require('child_process');
const chromium = require('chrome-aws-lambda');

exports.handle = async function (event) {
    if (event.warming) {
        return;
    }

    // Constant file where we write out options.
    const options = '/tmp/browsershot.js';

    event.options = event.options || {};

    // If there's a path, the developer is saving something to
    // a file. We'll write a constant path and let the PHP
    // side handle writing to the developer's disk.
    if (event.options.path) {
        event.options.path = '/tmp/browsershot';
    }

    if (event._html) {
        fs.writeFileSync('/tmp/index.html', event._html);
        event.url = 'file:///tmp/index.html';
    }

    // Get the executable path from the chrome layer.
    event.options.executablePath = await chromium.executablePath;

    // Combine the developers args with the ones from the layer.
    event.options.args = [
        ...(event.options.args || []),
        ...chromium.args,
    ];

    // Write the options to disk
    fs.writeFileSync(options, JSON.stringify(event));

    // Exec spatie's browser command.
    let result = execSync(`node ./browser.js '-f file://${options}'`);

    // If there was a path, then read the file and return it.
    if (event.options.path) {
        let contents = fs.readFileSync(event.options.path);
        fs.unlinkSync(event.options.path);

        return new Buffer(contents).toString('base64');
    }

    // Otherwise return the string.
    return result.toString();
};
