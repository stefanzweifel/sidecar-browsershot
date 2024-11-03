<?php

namespace Wnx\SidecarBrowsershot\Tests;

use Hammerstone\Sidecar\Providers\SidecarServiceProvider;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Imagick;
use Orchestra\Testbench\TestCase as Orchestra;
use PHPUnit\Framework\Assert;
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

    protected function updateCreationDateAndModDateOfPdf(string $pdf): string
    {
        return preg_replace([
            "#/CreationDate \(D:(\d){14}\+(\d){2}'(\d){2}'\)\\n#",
            "#/ModDate \(D:(\d){14}\+(\d){2}'(\d){2}'\)>>\\n#",
        ], [
            "/CreationDate (D:20230101000000+00'00')\n",
            "/ModDate (D:20230101000000+00'00')>>\n",
        ], $pdf, limit: 1);
    }

    public function assertPdfsAreSimilar(string $expected, string $actual, float $threshold = 0): void
    {
        $expectedPdf = new Imagick;
        $expectedPdf->readImageBlob($expected);
        $expectedPdf->resetIterator();
        $expectedPdf = $expectedPdf->appendImages(true);

        $actualPdf = new Imagick;
        $actualPdf->readImageBlob($actual);
        $actualPdf->resetIterator();
        $actualPdf = $actualPdf->appendImages(true);

        $diff = $expectedPdf->compareImages($actualPdf, imagick::METRIC_ABSOLUTEERRORMETRIC);

        $diffValue = $diff[1];

        // Assert that the difference is less than or equal to the threshold
        Assert::assertLessThanOrEqual($threshold, $diffValue, sprintf(
            'The PDFs are not similar (Difference: %d)', $diffValue
        ));
    }
}
