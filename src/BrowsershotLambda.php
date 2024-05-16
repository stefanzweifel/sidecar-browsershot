<?php

namespace Wnx\SidecarBrowsershot;

use Hammerstone\Sidecar\Results\SettledResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\ChromiumResult;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use Spatie\Browsershot\Exceptions\ElementNotFound;
use Wnx\SidecarBrowsershot\Functions\BrowsershotFunction;

class BrowsershotLambda extends Browsershot
{
    /**
     * @throws ElementNotFound
     */
    protected function callBrowser(array $command): string
    {
        $url = Arr::get($command, 'url');

        // If Browsershot should render arbitrary HTML, pass the HTML to the Lambda.
        // Lambda can't access the local file system.
        if (Str::startsWith($url, 'file://')) {
            $command['_html'] = file_get_contents($url);
        }

        /** @var SettledResult $response */
        $response = BrowsershotFunction::execute($command);

        if ($response->isError()) {
            $this->throwError($response);
        }

        // If the response is not valid JSON, it's probably a base64 encoded string representing a binary file.
        // In this case, we will return the base64 decoded string.
        if (json_decode($response->body(), true) === null) {
            $result = base64_decode($response->body());
        } else {
            $result = $response->body();

            // If the response is valid JSON, we can cast it to a Chromium Result.
            // It will contain the result and additional information about the Chromium process.
            if (is_array($chromiumResult = json_decode($response->body(), true))) {
                $this->chromiumResult = new ChromiumResult($chromiumResult);
            }
        }

        $s3 = Arr::get($command, 'options.s3');
        $path = Arr::get($command, 'options.path');

        if ($path && ! $s3) {
            file_put_contents($path, $result);

            return $path;
        }

        // If ChromiumResult is available, return the result from there.
        if ($this->chromiumResult) {
            return $this->chromiumResult->getResult();
        }

        // The result can now be either a base64 deocded string representing a binary file or a string representing
        // the ETag of a file on S3.
        return $result;
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

        $response->throw(2);
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
        /** @var string $fileContent */
        $fileContent = Storage::disk($disk)->get($imagePath);
        Storage::disk('local')->put($imagePath, $fileContent);

        $localPath = Storage::disk('local')->path($imagePath);

        $this->imageManipulations->apply($localPath);

        // Upload the manipulated image back to S3 and delete the temporary file.
        /** @var string $fileContent */
        $fileContent = Storage::disk('local')->get($imagePath);
        Storage::disk($disk)->put($imagePath, $fileContent);
        Storage::disk('local')->delete($imagePath);
    }

    /**
     * Tell BrowsershotLambda to load HTML from a file that is stored in S3.
     */
    public static function readHtmlFromS3(string $sourcePath, string $disk = 's3'): self
    {
        return (new BrowsershotLambda())
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
