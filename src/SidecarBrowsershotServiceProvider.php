<?php

namespace Wnx\SidecarBrowsershot;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wnx\SidecarBrowsershot\Commands\InternalBrowsershotSetupCommand;

class SidecarBrowsershotServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sidecar-browsershot')
            ->hasConfigFile()
            ->hasCommand(InternalBrowsershotSetupCommand::class);
    }

    public function bootingPackage(): void
    {
        // Make default fonts publishable for package consumers
        $this->publishes([
            __DIR__.'/../resources/lambda/fonts' => config('sidecar-browsershot.fonts'),
        ], 'sidecar-browsershot-fonts');
    }
}
