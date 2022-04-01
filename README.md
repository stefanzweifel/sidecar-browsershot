# Run Browsershot on AWS Lambda with Sidecar for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wnx/sidecar-browsershot.svg?style=flat-square)](https://packagist.org/packages/wnx/sidecar-browsershot)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/stefanzweifel/sidecar-browsershot/run-tests?label=tests)](https://github.com/stefanzweifel/sidecar-browsershot/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/stefanzweifel/sidecar-browsershot/Check%20&%20fix%20styling?label=code%20style)](https://github.com/stefanzweifel/sidecar-browsershot/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/wnx/sidecar-browsershot.svg?style=flat-square)](https://packagist.org/packages/wnx/sidecar-browsershot)

This package allows you to run [Browsershot](https://github.com/spatie/browsershot) on [AWS Lambda](https://aws.amazon.com/lambda/) through [Sidecar](https://github.com/hammerstonedev/sidecar).

You won't need to install Node, Puppeteer or Google Chrome on your server. The heavy lifting of booting a headless Google Chrome instance is happening on AWS Lambda.

## Requirements

This package requires that [`spatie/browsershot`](https://github.com/spatie/browsershot) and [`hammerstone/sidecar`](https://github.com/hammerstonedev/sidecar) have been installed in your Laravel application.

Follow their installation and configuration instructions. (You can skip the installation of puppeteer and Google Chrome for Browsershot though.)

## Installation

You can install the package via composer:

```bash
composer require wnx/sidecar-browsershot
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

See [Sidecar documentation](https://hammerstone.dev/sidecar/docs/main/functions/deploying) for details.

## Usage

You can use `BrowsershotLambda` like the default `Browsershot`-class coming from the Spatie package.
All you need to do is replace `Browsershot` with `BrowsershotLambda`.

```php
use Wnx\SidecarBrowsershot\BrowsershotLambda;

// an image will be saved
BrowsershotLambda::url('https://example.com')->save($pathToImage);

// a pdf will be saved
BrowsershotLambda::url('https://example.com')->save('example.pdf');

// save your own HTML to a PDF
BrowsershotLambda::html('<h1>Hello world!!</h1>')->save('example.pdf');

// Get HTML of a URL and store it on a given disk
$html = BrowsershotLambda::url('https://example.com')->bodyHtml();
Storage::disk('s3')->put('example.html', $html);
```

## Saving directly to S3

You can store your file directly on AWS S3 if you want to keep it there, or to avoid the size limit on Lambda responses.

You just need to pass a path and optional disk name (default: 's3') to the `saveToS3` method.
- You must have an S3 disk defined in config/filesystems.php
- You must give S3 write permissions to your sidecar-execution-role

```php
use Wnx\SidecarBrowsershot\BrowsershotLambda;

// an image will be saved on S3
BrowsershotLambda::url('https://example.com')->saveToS3('example.jpg');

// a pdf will be saved on S3
BrowsershotLambda::url('https://example.com')->saveToS3('example.pdf');

// save your own html to a PDF on S3
BrowsershotLambda::html('<h1>Hello world!!</h1>')->saveToS3('example.pdf', 'example-store');
```

## Testing

The testsuite makes connections to AWS and runs the deployed Lambda function. In order to run the testsuite, you will need an active [AWS account](https://aws.amazon.com/).

We can use the native `sidecar:configure` artisan command to create the necessary AWS credentials for Sidecar. First copy the `testbench.example.yaml` file to `testbench.yaml`.
Then run `./vendor/bin/testbench sidecar:configure` to start the Sidecar setup process. (You only have to do the setup once)

```bash
cp testbench.example.yaml testbench.yaml
cp .env.example .env
./vendor/bin/testbench sidecar:configure
```

After finishing the Sidecar setup process, you will have received a couple of `SIDECAR_*` environment variables. Add these credentials to `.env`.

Now we can deploy our local `BrowsershotFunction` to AWS Lambda. Run the following command in your terminal, before executing the testsuite.

```bash
./vendor/bin/testbench sidecar-browsershot:setup
```

After the successful deployment, you can run the testsuite.

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
- [Aaron Francis](https://github.com/aarondfrancis)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
