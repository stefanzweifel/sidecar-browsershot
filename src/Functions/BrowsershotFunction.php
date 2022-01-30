<?php

namespace Wnx\SidecarBrowsershot\Functions;

use Hammerstone\Sidecar\LambdaFunction;
use Hammerstone\Sidecar\Package;
use Hammerstone\Sidecar\Runtime;

class BrowsershotFunction extends LambdaFunction
{
    public function name()
    {
        return 'Browsershot Sidecar';
    }

    public function handler()
    {
        return 'resources/lambda/browsershot.handle';
    }

    public function package()
    {
        return Package::make()
            ->setBasePath(__DIR__ . '/../../')
            ->include([
                'resources/lambda/browser.js',
                'resources/lambda/browsershot.js',
            ]);
    }

    public function runtime()
    {
        return Runtime::NODEJS_14;
    }

    public function memory()
    {
        return 1024;
    }

    public function layers()
    {
        return [
            // https://github.com/shelfio/chrome-aws-lambda-layer
            'arn:aws:lambda:eu-central-1:764866452798:layer:chrome-aws-lambda:25',

            // Deploy my own layer?
            // https://github.com/pearljobs/chrome-aws-lambda-layer-action
            // https://github.com/pearljobs/chrome-aws-lambda-layer/blob/main/.github/workflows/publish.yml
        ];
    }
}
