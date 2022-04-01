<?php

namespace Wnx\SidecarBrowsershot;

use Hammerstone\Sidecar\Results\SettledResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use Spatie\Browsershot\Exceptions\ElementNotFound;
use Wnx\SidecarBrowsershot\Functions\BrowsershotFunction;

class BrowsershotLambda extends Browsershot
{
    protected function callBrowser(array $command): string
    {
        $url = Arr::get($command, 'url');

        // If Browsershot should render arbitrary HTML, pass the HTML to the Lambda.
        // Lambda can't access the local file system.
        if (Str::startsWith($url, 'file://')) {
            $command['_html'] = file_get_contents($url);
        }

        $response = BrowsershotFunction::execute($command);

        if ($response->isError()) {
            $this->throwError($response);
        }

        $path = Arr::get($command, 'options.path');
        $s3 = Arr::get($command, 'options.path');

        if ($path && !$s3) {
            file_put_contents($path, base64_decode($response->body()));
            return $path;
        } else {
            return $response->body();
        }
    }

    /**
     * @throws ElementNotFound
     */
    protected function throwError(SettledResult $response): void
    {
        $message = $response->errorAsString();

        if (Str::contains($message, 'Error: No node found for selector')) {
            $selector = Str::after($message, 'Error: No node found for selector: ');
            $selector = head(explode("\n", $selector));

            throw new ElementNotFound($selector);
        }

        $response->throw();
    }

    /**
     * @throws CouldNotTakeBrowsershot
     */
    protected function saveToS3(string $targetPath, string $disk = 's3')
    {
        $this->setOption('s3', [
            'path' => $targetPath,
            'region' => config('sidecar.region'),
            'bucket' => config("filesystems.disks.$disk.bucket")
        ]);

        $extension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

        if ($extension === '') {
            throw CouldNotTakeBrowsershot::outputFileDidNotHaveAnExtension($targetPath);
        }

        $command = $extension === 'pdf'
            ? $this->createPdfCommand($targetPath)
            : $this->createScreenshotCommand($targetPath);

        $result = $this->callBrowser($command);

        if (empty($result['ETag'])) {
            throw CouldNotTakeBrowsershot::chromeOutputEmpty("$targetPath on S3 disk: $disk", $command);
        }
    }
}
