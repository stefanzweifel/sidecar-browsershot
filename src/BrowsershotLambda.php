<?php

namespace Wnx\SidecarBrowsershot;

use Hammerstone\Sidecar\Results\SettledResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
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

        if ($path) {
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
}
