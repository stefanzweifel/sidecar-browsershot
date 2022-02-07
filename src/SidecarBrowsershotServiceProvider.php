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
            ->hasCommand(InternalBrowsershotSetupCommand::class);
    }
}
