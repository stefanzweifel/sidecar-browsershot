<?php

namespace Wnx\SidecarBrowsershot\Functions;

use Hammerstone\Sidecar\Architecture;
use Hammerstone\Sidecar\LambdaFunction;
use Hammerstone\Sidecar\Package;
use Hammerstone\Sidecar\WarmingConfig;
use Illuminate\Support\Str;

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
                __DIR__.'/../../resources/lambda/browsershot.js' => 'browsershot.js',
            ])
            ->includeExactly($this->customFonts());
    }

    protected function customFonts(): array
    {
        $fonts = collect();
        $fontDirectory = Str::finish(config('sidecar-browsershot.fonts'), DIRECTORY_SEPARATOR);

        // Check if the custom fonts folder exists.
        if (file_exists($fontDirectory)) {
            // Loop through all files in the custom fonts folder.

            /** @var array $fontFiles */
            $fontFiles = scandir($fontDirectory);

            foreach ($fontFiles as $file) {
                if (is_file($fontDirectory.$file)) {
                    $fonts->prepend("fonts/$file", $fontDirectory.$file);
                }
            }
        }

        // By default, we include the NotoColorEmoji font.
        $fonts->prepend('fonts/NotoColorEmoji.ttf', __DIR__.'/../../resources/lambda/fonts/NotoColorEmoji.ttf');

        // Ensure that we only have unique font values.
        return $fonts->unique()->toArray();
    }

    /**
     * We get puppeteer out of the layer, which Spatie doesn't allow
     * for. We'll just overwrite their browser.cjs to add it.
     *
     * @return string
     */
    protected function modifiedBrowserJs()
    {
        if (app()->environment('testing')) {
            /** @var string $browser */
            $browser = file_get_contents('vendor/spatie/browsershot/bin/browser.cjs');
        } else {
            /** @var string $browser */
            $browser = file_get_contents(base_path('vendor/spatie/browsershot/bin/browser.cjs'));
        }

        // Remove their reference.
        $browser = str_replace('const puppet = (pup || require(\'puppeteer\'));', '', $browser);

        // Use pupeteer-core instead.
        return "const puppet = require('puppeteer-core'); \n".$browser;
    }

    /**
     * {@inheritDoc}
     */
    public function runtime()
    {
        return 'nodejs20.x';
    }

    /**
     * {@inheritDoc}
     */
    public function memory()
    {
        return config('sidecar-browsershot.memory');
    }

    /**
     * {@inheritDoc}
     */
    public function storage()
    {
        // Defaults to the main sidecar config value if the sidecar-browsershot config hasn't been updated to include this new key.
        return config('sidecar-browsershot.storage', parent::storage());
    }

    /**
     * {@inheritDoc}
     */
    public function timeout()
    {
        // Defaults to the main sidecar config value if the sidecar-browsershot config hasn't been updated to include this new key.
        return config('sidecar-browsershot.timeout', parent::timeout());
    }

    /**
     * {@inheritDoc}
     */
    public function architecture()
    {
        return Architecture::X86_64;
    }

    /**
     * {@inheritDoc}
     */
    public function warmingConfig()
    {
        return WarmingConfig::instances(config('sidecar-browsershot.warming'));
    }

    public function layers()
    {
        if ($layers = config('sidecar-browsershot.layers')) {
            return $layers;
        }

        $region = config('sidecar.aws_region');

        if ($region === 'ap-northeast-2') {
            $chromeAwsLambdaVersion = 41;
        } else {
            $chromeAwsLambdaVersion = 42;
        }

        // Add Layers that each contain `puppeteer-core` and `@sparticuz/chromium`
        // https://github.com/stefanzweifel/sidecar-browsershot-layer
        // https://github.com/shelfio/chrome-aws-lambda-layer
        return [
            "arn:aws:lambda:{$region}:821527532446:layer:sidecar-browsershot-layer:2",
            "arn:aws:lambda:{$region}:764866452798:layer:chrome-aws-lambda:{$chromeAwsLambdaVersion}",
        ];
    }
}
