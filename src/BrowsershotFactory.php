<?php

namespace Wnx\SidecarBrowsershot;

use Spatie\Browsershot\Browsershot;
use Wnx\SidecarBrowsershot\Functions\BrowsershotFunction;

class BrowsershotFactory
{
    public function create(Browsershot $browsershot)
    {
        // IF PDF should be generated
        $command = $browsershot->createPdfCommand();

        // If Screenshot should be generated
        // $command = $browsershot->createScreenshotCommand();

        // If HTML is provided
        // $command['html'] = base64_encode('<h1>Hello World</h1>');

        $result = BrowsershotFunction::execute([
            'command' => $command,

            // If HTML should be generated
            // 'command' => $browsershot->createBodyHtmlCommand(),
        ])->throw();

        return base64_decode($result->body());
    }
}
