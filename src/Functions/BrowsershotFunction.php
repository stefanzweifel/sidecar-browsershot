<?php

namespace Wnx\SidecarBrowsershot\Functions;

use Hammerstone\Sidecar\LambdaFunction;
use Hammerstone\Sidecar\Package;
use Hammerstone\Sidecar\Runtime;

class BrowsershotFunction extends LambdaFunction
{
    public function handler()
    {
        return 'browsershot.handle';
    }

    public function name()
    {
        return 'browsershot';
    }

    public function package()
    {

        return Package::make()
            ->includeStrings([
                'browser.js' => $this->modifiedBrowserJs()
            ])
            ->includeExactly([
                __DIR__ . '/../../resources/lambda/browsershot.js' => 'browsershot.js',
            ]);
    }

    /**
     * We get puppeteer out of the layer, which spatie doesn't allow
     * for. We'll just overwrite their browser.js to add it.
     *
     * @return string
     */
    protected function modifiedBrowserJs()
    {
        $browser = file_get_contents(base_path('vendor/spatie/browsershot/bin/browser.js'));

        // Remove their reference.
        $browser = str_replace('const puppet = (pup || require(\'puppeteer\'));', '', $browser);

        // Add ours.
        return "const puppet = require('chrome-aws-lambda').puppeteer; \n" . $browser;
    }

    public function runtime()
    {
        return Runtime::NODEJS_14;
    }

    public function memory()
    {
        return 2048;
    }

    public function layers()
    {
        $region = config('sidecar.aws_region');

        return [
            // https://github.com/shelfio/chrome-aws-lambda-layer
            "arn:aws:lambda:{$region}:764866452798:layer:chrome-aws-lambda:25",
        ];
    }
}
