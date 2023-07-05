<?php

namespace Wnx\SidecarBrowsershot\Functions;

use Hammerstone\Sidecar\Architecture;
use Hammerstone\Sidecar\LambdaFunction;
use Hammerstone\Sidecar\Package;
use Hammerstone\Sidecar\Runtime;
use Hammerstone\Sidecar\WarmingConfig;

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
                'browser.cjs' => $this->modifiedBrowserJs(),
            ])
            ->includeExactly([
                __DIR__ . '/../../resources/lambda/browsershot.js' => 'browsershot.js',
                __DIR__ . '/../../resources/lambda/NotoColorEmoji.ttf' => 'NotoColorEmoji.ttf',
            ]);
    }

    /**
     * We get puppeteer out of the layer, which spatie doesn't allow
     * for. We'll just overwrite their browser.cjs to add it.
     *
     * @return string
     */
    protected function modifiedBrowserJs()
    {
        if (app()->environment('testing')) {
            $browser = file_get_contents('vendor/spatie/browsershot/bin/browser.cjs');
        } else {
            $browser = file_get_contents(base_path('vendor/spatie/browsershot/bin/browser.cjs'));
        }

        // Remove their reference.
        $browser = str_replace('const puppet = (pup || require(\'puppeteer\'));', '', $browser);

        // Add ours.
        return "const puppet = require('@sparticuz/chrome-aws-lambda').puppeteer; \n" . $browser;
    }

    public function runtime()
    {
        return Runtime::NODEJS_14;
    }

    public function memory()
    {
        return config('sidecar-browsershot.memory');
    }

    /**
     * @inheritDoc
     */
    public function storage()
    {
        // Default to the main sidecar config value if the sidecar-browsershot config hasn't been updated to include this new key.
        return config('sidecar-browsershot.storage', parent::storage());
    }

    /**
     * @inheritDoc
     */
    public function architecture()
    {
        return Architecture::X86_64;
    }

    public function warmingConfig()
    {
        return WarmingConfig::instances(config('sidecar-browsershot.warming'));
    }

    public function layers()
    {
        if ($layer = config('sidecar-browsershot.layer')) {
            return [$layer];
        }

        $region = config('sidecar.aws_region');

        // https://github.com/shelfio/chrome-aws-lambda-layer
        return ["arn:aws:lambda:{$region}:764866452798:layer:chrome-aws-lambda:31"];
    }
}
