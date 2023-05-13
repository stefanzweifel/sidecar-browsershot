<?php

namespace Wnx\SidecarBrowsershot;

use Hammerstone\Sidecar\Results\SettledResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use Spatie\Browsershot\Exceptions\ElementNotFound;
use Spatie\Image\Image;
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

        $s3 = Arr::get($command, 'options.s3');
        $path = Arr::get($command, 'options.path');

        if ($path && ! $s3) {
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
    public function saveToS3(string $targetPath, string $disk = 's3'): string
    {
        $this->setOption('s3', [
            'path' => $targetPath,
            'region' => config('sidecar.aws_region'),
            'bucket' => config("filesystems.disks.$disk.bucket"),
        ]);

        $extension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

        if ($extension === '') {
            throw CouldNotTakeBrowsershot::outputFileDidNotHaveAnExtension($targetPath);
        }

        $command = $extension === 'pdf'
            ? $this->createPdfCommand($targetPath)
            : $this->createScreenshotCommand($targetPath);

        $output = $this->callBrowser($command);

        if (empty($output)) {
            throw CouldNotTakeBrowsershot::chromeOutputEmpty("$targetPath on S3 disk: $disk", $output, $command);
        }

        if (! $this->imageManipulations->isEmpty()) {
            $this->applyManipulationsOnS3($targetPath, $disk);
        }

        return $output;
    }

    public function applyManipulationsOnS3(string $imagePath, string $disk = 's3'): void
    {
        // Download the image from S3 to a temporary file and apply the manipulations.
        Storage::disk('local')->put($imagePath, Storage::disk($disk)->get($imagePath));

        $localPath = Storage::disk('local')->path($imagePath);

        $this->applyManipulations($localPath);

        // Upload the manipulated image back to S3 and delete the temporary file.
        Storage::disk($disk)->put($imagePath, Storage::disk('local')->get($imagePath));
        Storage::disk('local')->delete($imagePath);
    }

    /**
     * Tell BrowsershotLambda to load HTML from a file that is stored in S3.
     */
    public static function readHtmlFromS3(string $sourcePath, string $disk = 's3'): self
    {
        return (new static())
            ->setOption('s3Source', [
                'path' => $sourcePath,
                'region' => config('sidecar.aws_region'),
                'bucket' => config("filesystems.disks.$disk.bucket"),
            ]);
    }

    public function base64pdf(): string
    {
        $command = $this->createPdfCommand();

        return rtrim($this->callBrowser($command));
    }
}
