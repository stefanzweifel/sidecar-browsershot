const fs = require('fs');
const { execSync } = require('child_process');
const chromium = require('@sparticuz/chromium');
const { S3 } = require("@aws-sdk/client-s3");

exports.handle = async function (event) {
    if (event.warming) {
        return;
    }

    // sparticuz/chromium specific settings.
    // https://github.com/Sparticuz/chromium#usage
    // Use legacy headless mode.
    chromium.setHeadlessMode = true;
    // Disable webgl.
    chromium.setGraphicsMode = false;

    // Add custom fonts to chromium.
    const fontsFileNames = fs.readdirSync('/var/task/fonts/');
    for (const fontFileName of fontsFileNames) {
        await chromium.font(`/var/task/fonts/${fontFileName}`);
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

    // If arbitrary HTML should be rendered, store the passed
    // HTML in a temporary file and use the path as the URL.
    if (event._html) {
        fs.writeFileSync('/tmp/index.html', event._html);
        event.url = 'file:///tmp/index.html';
    } else if (event.options.s3Source) {
        // If the source is S3, then download the file into a temporary file to be used as the URL.
        const s3 = new S3({
            region: event.options.s3Source.region,
            accessKeyId: event.options.s3Source.key,
            secretAccessKey: event.options.s3Source.secret,
        });

        const params = {
            Bucket: event.options.s3Source.bucket,
            Key: event.options.s3Source.path,
        }

        const result = await s3.getObject(params);
        const fileContents = await streamToString(result.Body);
        fs.writeFileSync('/tmp/index.html', fileContents);

        event.url = 'file:///tmp/index.html';
    }
    // Get the executable path from the chrome layer.
    event.options.executablePath = await chromium.executablePath();

    // Combine the developers args with the ones from the layer.
    event.options.args = [
        ...(event.options.args || []),
        ...chromium.args,
    ];

    // Write the options to disk
    fs.writeFileSync(options, JSON.stringify(event));

    // Exec spatie's browser command.
    let result = execSync(`node ./browser.cjs '-f file://${options}'`, {
        // Set maxBuffer to 100 MB
        maxBuffer: 1024 * 1024 * 100
    });

    // Delete puppeteer profiles from temp directory to free up space
    fs.readdirSync('/tmp').forEach(file => {
        if (file.startsWith('puppeteer_dev_chrome_profile')) {
            fs.rmdirSync(`/tmp/${file}`, { recursive: true });
        }
    });

    // If there was a path, then read the file and return it.
    if (event.options.path) {
        let contents = fs.readFileSync(event.options.path);

        fs.unlinkSync(event.options.path);

        // If the file destination is S3, then write
        // the file and return the ETag as confirmation.
        if (event.options.s3) {
            const accessKeyId = event.options.s3.key;
            const secretAccessKey = event.options.s3.secret;
            const s3 = new S3({
                region: event.options.s3.region,
                accessKeyId: event.options.s3.key,
                secretAccessKey: event.options.s3.secret,
            });

            let type;

            switch (event.options.type) {
                case "png":
                    type = 'image/png';
                    break;
                case "jpeg":
                    type = 'image/jpeg';
                    break;
                default:
                    type = 'application/pdf';
            }

            const params = {
                Bucket: event.options.s3.bucket,
                Key: event.options.s3.path,
                Body: contents,
                ContentType: type,
            }

            const result = await s3.putObject(params);

            return result.ETag;
        }

        return new Buffer.from(contents).toString('base64');
    }

    // Otherwise, return the string.
    return result.toString();
};

/**
 * Read a stream into a string.
 */
const streamToString = function(stream) {
    return new Promise((resolve, reject) => {
        const chunks = [];
        stream.on('data', (chunk) => chunks.push(chunk));
        stream.on('error', reject);
        stream.on('end', () => resolve(Buffer.concat(chunks).toString('utf8')));
    });
};
