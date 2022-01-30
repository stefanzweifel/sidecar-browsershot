# Run Browsershot with Sidecar

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wnx/sidecar-browsershot.svg?style=flat-square)](https://packagist.org/packages/wnx/sidecar-browsershot)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/stefanzweifel/sidecar-browsershot/run-tests?label=tests)](https://github.com/stefanzweifel/sidecar-browsershot/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/stefanzweifel/sidecar-browsershot/Check%20&%20fix%20styling?label=code%20style)](https://github.com/stefanzweifel/sidecar-browsershot/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/wnx/sidecar-browsershot.svg?style=flat-square)](https://packagist.org/packages/wnx/sidecar-browsershot)

> 🚨 This is a work in progress!

This package allows you to run [`spatie/browsershot`](https://github.com/spatie/browsershot) through [`hammerstone/sidecar`](https://github.com/hammerstonedev/sidecar) on AWS Lambda.
This means that you don't have to install Chrome or Puppeteer on your server.    
The browser runs on AWS Lambda. 

Before you begin:

- This package uses its own `browser.js` to control Puppeteer. It's possible that a new feature of `spatie/browsershot` has not made it yet into this package. (The goal is to use the original `spatie/browsershot` `browser.js` file for feature parity)
- The Lambda function currently disables Chromes Sandbox in order to be executed on AWS Lambda.

## Installation

You can install the package via composer:

```bash
composer require wnx/sidecar-browsershot
```
(Note that both [`spatie/browsershot`](https://github.com/spatie/browsershot) and [`hammerstone/sidecar`](https://github.com/hammerstonedev/sidecar) must be installed too.)

You can publish the config file with:

```bash
php artisan vendor:publish --tag="sidecar-browsershot-config"
```

This is the contents of the published config file:

```php
return [
];
```

Register the `BrowsershotFunction::class` in your `sidecar.php` config file.

```php
/*
 * All of your function classes that you'd like to deploy go here.
 */
'functions' => [
    \Wnx\SidecarBrowsershot\Functions\BrowsershotFunction::class,
],
```

Deploy the Lambda function by running:

```shell
php artisan sidecar:deploy --activate
```

See [Sidecar docs](https://hammerstone.dev/sidecar/docs/main/functions/deploying) for details.

## Usage

> - TBD
> - `save()`-methods coming from `spatie/browsershot` do not work yet. You are responsible to store the file on a filesystem.

```php
use Wnx\SidecarBrowsershot\BrowsershotLambda;

$html = BrowsershotLambda::url('https://en.wikipedia.org/wiki/Special:Random')->bodyHtml();
File::put('example.html', $html);

$screenshot = BrowsershotLambda::url('https://en.wikipedia.org/wiki/Special:Random')->screenshot();
File::put('example.jpg', $screenshot);

$pdf = BrowsershotLambda::url('https://en.wikipedia.org/wiki/Special:Random')->pdf();
File::put('example.pdf', $pdf);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Stefan Zweifel](https://github.com/stefanzweifel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
