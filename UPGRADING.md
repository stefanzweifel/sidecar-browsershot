# Upgrading

## From v1 to v2

### Dependency Changes

`wnx/sidecar-browsershot` v2 now requires `spatie/browsershot` [v4](https://github.com/spatie/browsershot/releases/tag/4.0.0).
See their [upgrade guide](https://spatie.be/docs/browsershot/v4/upgrading) for more details on how this might impact your project.

### Image Manipulation

In v4 of `spatie/browsershot`, `spatie/image` is now an optional dependency. If you want to manipulate images, you have to add `spatie/image` as a dependency in your project.

```shell
composer require spatie/image
```

The FQDN of some classes/enums has changes as well. If you've used image manipulations, update your code accordingly. Here is an example:

```diff
 BrowsershotLambda::url('https://example.com')
     ->windowSize(1920, 1080)
-    ->fit(\Spatie\Image\Manipulations::FIT_CONTAIN, 200, 200)
+    ->fit(\Spatie\Image\Enums\Fit::Contain, 200, 200)
     ->save('example.jpg');
```


### Configuration change

The `sidecar-browsershot.layer` configuration has been renamed to `sidecar-browsershot.layers` and now accepts an array of Amazon Resource Names (ARN); instead of just one ARN.
If you haven't published the configuration file in the first place, there's nothing you have to do.

If you've published or modified the config file or used the `SIDECAR_BROWSERSHOT_LAYER` env variable, we suggest you republish the config file using the following command.

```shell
php artisan vendor:publish --provider="Wnx\SidecarBrowsershot\SidecarBrowsershotServiceProvider" --force
```

Since [v1.10.0](https://github.com/stefanzweifel/sidecar-browsershot/releases/tag/v1.10.0) `sidecar-browsershot` relies on 2 AWS Layers: "shelfio/chrome-aws-lambda-layer" and "sidecar-browsershot-layer". ("shelfio/chrome-aws-lambda-layer" contains the Chromium binary; "sidecar-browsershot-layer" contains `puppeteer-core`)

If you decide to customize the layers in your project, you MUST ensure that "shelfio/chrome-aws-lambda-layer" and "sidecar-browsershot-layer" are included as well.
A possible configuration might look like this:

```php
    'layers' => [
        "arn:aws:lambda:us-east-1:821527532446:layer:sidecar-browsershot-layer:1", // ← Required
        "arn:aws:lambda:us-east-1:764866452798:layer:chrome-aws-lambda:37", // ← Required
        "arn:aws:lambda:us-east-1:123456789000:layer:your-custom-layer:1", // Your custom layer
    ],
```
