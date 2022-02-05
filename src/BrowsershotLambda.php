<?php

namespace Wnx\SidecarBrowsershot;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\Exceptions\ElementNotFound;
use Wnx\SidecarBrowsershot\Functions\BrowsershotFunction;

class BrowsershotLambda extends Browsershot
{
    protected function callBrowser(array $command)
    {
        $url = Arr::get($command, 'url');

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
        } else {
            return $response->body();
        }
    }

    protected function throwError($response)
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
