<?php

use Wnx\SidecarBrowsershot\Functions\BrowsershotFunction;

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
