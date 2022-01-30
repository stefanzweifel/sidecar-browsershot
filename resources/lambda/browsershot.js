exports.handle = async function (event) {
    if (event.warming) {
        return;
    }

    const browser = require('./browser.js');

    return browser.callChrome(event.command);
};
