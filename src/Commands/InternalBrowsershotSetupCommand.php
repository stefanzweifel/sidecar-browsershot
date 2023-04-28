<?php

namespace Wnx\SidecarBrowsershot\Commands;

use Hammerstone\Sidecar\Commands\Deploy;
use Illuminate\Console\Command;
use Wnx\SidecarBrowsershot\Functions\BrowsershotFunction;

class InternalBrowsershotSetupCommand extends Command
{
    public $signature = 'sidecar-browsershot:setup';

    public $description = 'Deploy and activate BrowsershotLambda function to AWS. (Only used for local testing purposes)';

    protected $hidden = true;

    public function handle(): int
    {
        $region = env('SIDECAR_REGION');
        $bucket = env('SIDECAR_ARTIFACT_BUCKET_NAME');

        config()->set('sidecar.functions', [BrowsershotFunction::class]);
        config()->set('sidecar.env', 'testing');
        config()->set('sidecar.aws_key', env('SIDECAR_ACCESS_KEY_ID'));
        config()->set('sidecar.aws_secret', env('SIDECAR_SECRET_ACCESS_KEY'));
        config()->set('sidecar.aws_region', $region);
        config()->set('sidecar.aws_bucket', $bucket);
        config()->set('sidecar.execution_role', env('SIDECAR_EXECUTION_ROLE'));

        $deploy = $this->confirm("Deploy Lambda function to {$region} and bucket {$bucket}?", true);
        $this->info("Deploying Lambda function to {$region} and bucket {$bucket}.");

        if (! $deploy) {
            $this->info('Nothing deployed.');

            return self::SUCCESS;
        }

        $this->info('Deploy function …');

        $exitCode = $this->call(Deploy::class, [
            '--activate' => true,
            '--env' => 'testing',
        ]);

        if ($exitCode !== self::SUCCESS) {
            $this->error('Deploy command failed');

            return self::FAILURE;
        }

        $this->comment('All done');

        return self::SUCCESS;
    }
}
