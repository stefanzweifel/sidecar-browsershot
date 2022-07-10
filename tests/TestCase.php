<?php

namespace Wnx\SidecarBrowsershot\Tests;

use Hammerstone\Sidecar\Providers\SidecarServiceProvider;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Orchestra\Testbench\TestCase as Orchestra;
use Wnx\SidecarBrowsershot\Functions\BrowsershotFunction;
use Wnx\SidecarBrowsershot\SidecarBrowsershotServiceProvider;

class TestCase extends Orchestra
{
    protected $loadEnvironmentVariables = true;

    protected function getPackageProviders($app)
    {
        return [
            SidecarBrowsershotServiceProvider::class,
            SidecarServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        config()->set('sidecar.functions', [BrowsershotFunction::class]);
        config()->set('sidecar.env', 'testing');
        config()->set('sidecar.aws_key', env('SIDECAR_ACCESS_KEY_ID'));
        config()->set('sidecar.aws_secret', env('SIDECAR_SECRET_ACCESS_KEY'));
        config()->set('sidecar.aws_region', env('SIDECAR_REGION'));
        config()->set('sidecar.aws_bucket', env('SIDECAR_ARTIFACT_BUCKET_NAME'));
        config()->set('sidecar.execution_role', env('SIDECAR_EXECUTION_ROLE'));

        // Setup S3 bucket
        config()->set('filesystems.disks.s3.driver', 's3');
        config()->set('filesystems.disks.s3.key', env('SIDECAR_ACCESS_KEY_ID'));
        config()->set('filesystems.disks.s3.secret', env('SIDECAR_SECRET_ACCESS_KEY'));
        config()->set('filesystems.disks.s3.region', env('SIDECAR_REGION'));
        config()->set('filesystems.disks.s3.bucket', env('SIDECAR_ARTIFACT_BUCKET_NAME'));
    }
}
