<?php

namespace Wnx\SidecarBrowsershot\Functions;

use Hammerstone\Sidecar\Architecture;
use Hammerstone\Sidecar\LambdaFunction;
use Hammerstone\Sidecar\Package;
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
        $package = Package::make()
            ->includeStrings([
                'browser.cjs' => $this->modifiedBrowserJs(),
            ])
            ->includeExactly([
                __DIR__ . '/../../resources/lambda/browsershot.js' => 'browsershot.js',
            ]);
        return $this->packageFonts($package);
    }

    protected function packageFonts(Package $package): Package
    {
        $fontResourcePath = config('sidecar-browsershot.fonts', 'sidecar-browsershot/fonts');
        $fontResourceDir = resource_path($fontResourcePath);

        if (! file_exists($fontResourceDir)) {
            $package->includeExactly([
                __DIR__ . '/../../resources/lambda/fonts/NotoColorEmoji.ttf' => 'fonts/NotoColorEmoji.ttf',
            ]);
        } else {
            foreach (scandir($fontResourceDir) as $file) {
                if (is_file($fontResourceDir . $file)) {
                    $package->includeExactly([
                        "$fontResourceDir/$file" => "fonts/$file",
                    ]);
                }
            }
        }
        return $package;
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

        // Use pupeteer-core instead.
        return "const puppet = require('puppeteer-core'); \n" . $browser;
    }

    public function runtime()
    {
        return 'nodejs18.x';
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
        // Defaults to the main sidecar config value if the sidecar-browsershot config hasn't been updated to include this new key.
        return config('sidecar-browsershot.storage', parent::storage());
    }

    /**
     * @inheritDoc
     */
    public function timeout()
    {
        // Defaults to the main sidecar config value if the sidecar-browsershot config hasn't been updated to include this new key.
        return config('sidecar-browsershot.timeout', parent::timeout());
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

        if ($region === 'ap-northeast-2') {
            $chromeAwsLambdaVersion = 36;
        } else {
            $chromeAwsLambdaVersion = 37;
        }


        // Add Layers that each contain `puppeteer-core` and `@sparticuz/chromium`
        // https://github.com/stefanzweifel/sidecar-browsershot-layer
        // https://github.com/shelfio/chrome-aws-lambda-layer
        return [
            "arn:aws:lambda:{$region}:821527532446:layer:sidecar-browsershot-layer:1",
            "arn:aws:lambda:{$region}:764866452798:layer:chrome-aws-lambda:{$chromeAwsLambdaVersion}",
        ];
    }
}
