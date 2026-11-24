<?php

use Hammerstone\Sidecar\Sidecar;
use Hammerstone\Sidecar\Results\SettledResult;
use Wnx\SidecarBrowsershot\BrowsershotLambda;
use Wnx\SidecarBrowsershot\Functions\BrowsershotFunction;

function capturePayload(): array
{
    $payload = null;

    $result = Mockery::mock(SettledResult::class);
    $result->shouldReceive('isError')->andReturn(false);
    $result->shouldReceive('body')->andReturn('<html><body>Hello</body></html>');

    Sidecar::shouldReceive('execute')
        ->once()
        ->withArgs(function ($function, $command) use (&$payload) {
            $payload = $command;

            return $function === BrowsershotFunction::class;
        })
        ->andReturn($result);

    BrowsershotLambda::html('<p>Hello</p>')->bodyHtml();

    return $payload;
}

it('asks the Lambda to remove crash dumps by default', function () {
    expect(capturePayload())->toHaveKey('_keepCrashDumps', false);
});

it('asks the Lambda to keep crash dumps when configured', function () {
    config()->set('sidecar-browsershot.keep_crash_dumps', true);

    expect(capturePayload())->toHaveKey('_keepCrashDumps', true);
});
