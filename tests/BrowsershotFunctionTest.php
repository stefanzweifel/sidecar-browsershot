<?php

use Wnx\SidecarBrowsershot\Functions\BrowsershotFunction;

it('uses the current regional Chromium layer and preserves the Puppeteer layer', function (string $region, int $chromiumVersion, int $puppeteerVersion) {
    config()->set('sidecar.aws_region', $region);
    config()->set('sidecar-browsershot.layers', []);

    expect((new BrowsershotFunction)->layers())->toBe([
        "arn:aws:lambda:{$region}:821527532446:layer:sidecar-browsershot-layer:{$puppeteerVersion}",
        "arn:aws:lambda:{$region}:764866452798:layer:chrome-aws-lambda:{$chromiumVersion}",
    ]);
})->with([
    'US East' => ['us-east-1', 117, 8],
    'EU Central' => ['eu-central-1', 117, 7],
    'Seoul' => ['ap-northeast-2', 116, 7],
]);

function modifiedBrowserJs(): string
{
    $method = new ReflectionMethod(BrowsershotFunction::class, 'modifiedBrowserJs');

    return $method->invoke(new BrowsershotFunction);
}

it('loads puppeteer-core instead of puppeteer in browser.cjs', function () {
    $browser = modifiedBrowserJs();

    expect($browser)
        ->not->toMatch('/require(\.resolve)?\([\'"]puppeteer[\'"]\)/')
        ->toContain("require('puppeteer-core')");
});
