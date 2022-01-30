const chromium = require('chrome-aws-lambda');
const puppet = chromium.puppeteer;
const URLParse = require('url').parse;

exports.callChrome = async (request) => {
    let browser;
    let page;
    let output;
    const requestsList = [];

    const getOutput = async (page, request) => {
        let output;

        if (request.action == 'requestsList') {
            output = JSON.stringify(requestsList);

            return output;
        }

        if (request.action == 'evaluate') {
            output = await page.evaluate(request.options.pageFunction);

            return output;
        }

        output = await page[request.action](request.options);

        return output.toString('base64');
    };

    try {
        browser = await puppet.launch({
            ignoreHTTPSErrors: request.options.ignoreHttpsErrors,
            executablePath: await chromium.executablePath,
            headless: chromium.headless,

            args: [
                // These 3 args are required to run to boot chromium on AWS Lambda
                // https://github.com/puppeteer/puppeteer/issues/1947#issuecomment-879500626
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--single-process',
            ].concat(request.options.args || []),
            // pipe: request.options.pipe || false,
            env: {
                ...(request.options.env || {}),
                ...process.env,
            },
        });

        page = await browser.newPage();

        if (request.options && request.options.disableJavascript) {
            await page.setJavaScriptEnabled(false);
        }

        await page.setRequestInterception(true);

        if (request.postParams) {
            const postParamsArray = request.postParams;
            const queryString = Object.keys(postParamsArray)
                .map((key) => `${key}=${postParamsArray[key]}`)
                .join('&');
            page.once('request', (interceptedRequest) => {
                interceptedRequest.continue({
                    method: 'POST',
                    postData: queryString,
                    headers: {
                        ...interceptedRequest.headers(),
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                });
            });
        }

        page.on('request', (request) => {
            requestsList.push({
                url: request.url(),
            });
            request.continue();
        });

        if (request.options && request.options.disableImages) {
            page.on('request', (request) => {
                if (request.resourceType() === 'image') request.abort();
                else request.continue();
            });
        }

        if (request.options && request.options.blockDomains) {
            var domainsArray = JSON.parse(request.options.blockDomains);
            page.on('request', (request) => {
                const hostname = URLParse(request.url()).hostname;
                domainsArray.forEach(function (value) {
                    if (hostname.indexOf(value) >= 0) request.abort();
                });
                request.continue();
            });
        }

        if (request.options && request.options.blockUrls) {
            var urlsArray = JSON.parse(request.options.blockUrls);
            page.on('request', (request) => {
                urlsArray.forEach(function (value) {
                    if (request.url().indexOf(value) >= 0) request.abort();
                });
                request.continue();
            });
        }

        if (request.options && request.options.dismissDialogs) {
            page.on('dialog', async (dialog) => {
                await dialog.dismiss();
            });
        }

        if (request.options && request.options.userAgent) {
            await page.setUserAgent(request.options.userAgent);
        }

        if (request.options && request.options.device) {
            const devices = puppet.devices;
            const device = devices[request.options.device];
            await page.emulate(device);
        }

        if (request.options && request.options.emulateMedia) {
            await page.emulateMediaType(request.options.emulateMedia);
        }

        if (request.options && request.options.viewport) {
            await page.setViewport(request.options.viewport);
        }

        if (request.options && request.options.extraHTTPHeaders) {
            await page.setExtraHTTPHeaders(request.options.extraHTTPHeaders);
        }

        if (request.options && request.options.authentication) {
            await page.authenticate(request.options.authentication);
        }

        if (request.options && request.options.cookies) {
            await page.setCookie(...request.options.cookies);
        }

        if (request.options && request.options.timeout) {
            await page.setDefaultNavigationTimeout(request.options.timeout);
        }

        const requestOptions = {};

        if (request.options && request.options.networkIdleTimeout) {
            requestOptions.waitUntil = 'networkidle';
            requestOptions.networkIdleTimeout = request.options.networkIdleTimeout;
        } else if (request.options && request.options.waitUntil) {
            requestOptions.waitUntil = request.options.waitUntil;
        }

        if (request.html) {
            const html = Buffer.from(request.html, 'base64').toString();
            await page.setContent(html);
        } else {
            await page.goto(request.url, requestOptions);
        }

        if (request.options && request.options.disableImages) {
            await page.evaluate(() => {
                let images = document.getElementsByTagName('img');
                while (images.length > 0) {
                    images[0].parentNode.removeChild(images[0]);
                }
            });
        }

        if (request.options && request.options.types) {
            for (let i = 0, len = request.options.types.length; i < len; i++) {
                let typeOptions = request.options.types[i];
                await page.type(typeOptions.selector, typeOptions.text, {
                    delay: typeOptions.delay,
                });
            }
        }

        if (request.options && request.options.selects) {
            for (let i = 0, len = request.options.selects.length; i < len; i++) {
                let selectOptions = request.options.selects[i];
                await page.select(selectOptions.selector, selectOptions.value);
            }
        }

        if (request.options && request.options.clicks) {
            for (let i = 0, len = request.options.clicks.length; i < len; i++) {
                let clickOptions = request.options.clicks[i];
                await page.click(clickOptions.selector, {
                    button: clickOptions.button,
                    clickCount: clickOptions.clickCount,
                    delay: clickOptions.delay,
                });
            }
        }

        if (request.options && request.options.addStyleTag) {
            await page.addStyleTag(JSON.parse(request.options.addStyleTag));
        }

        if (request.options && request.options.addScriptTag) {
            await page.addScriptTag(JSON.parse(request.options.addScriptTag));
        }

        if (request.options.delay) {
            await page.waitForTimeout(request.options.delay);
        }

        if (request.options.selector) {
            var element;
            const index = request.options.selectorIndex || 0;
            if (index) {
                element = await page.$$(request.options.selector);
                if (!element.length || typeof element[index] === 'undefined') {
                    element = null;
                } else {
                    element = element[index];
                }
            } else {
                element = await page.$(request.options.selector);
            }
            if (element === null) {
                throw { type: 'ElementNotFound' };
            }

            request.options.clip = await element.boundingBox();
        }

        if (request.options.function) {
            let functionOptions = {
                polling: request.options.functionPolling,
                timeout: request.options.functionTimeout || request.options.timeout,
            };
            await page.waitForFunction(request.options.function, functionOptions);
        }

        output = await getOutput(page, request);

        await browser.close();
    } catch (exception) {
        if (browser) {
            await browser.close();
        }

        console.error(exception);

        if (exception.type === 'ElementNotFound') {
            process.exit(2);
        }

        process.exit(1);
    }

    return output;
};
