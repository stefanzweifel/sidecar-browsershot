<?php

use Hammerstone\Sidecar\Exceptions\LambdaExecutionException;
use function Pest\Laravel\artisan;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

beforeEach(function () {
    artisan('sidecar:deploy --activate --env=testing');

    if (file_exists('example.pdf')) {
        unlink('example.pdf');
    }
    if (file_exists('example.jpg')) {
        unlink('example.jpg');
    }
});

afterAll(function () {
    if (file_exists('example.pdf')) {
        unlink('example.pdf');
    }
    if (file_exists('example.jpg')) {
        unlink('example.jpg');
    }
});

it('generates screenshot from url', function () {
    $this->assertFileDoesNotExist('example.jpg');
    BrowsershotLambda::url('https://example.com')->save('example.jpg');
    $this->assertFileExists('example.jpg');
});

it('generates screenshot from html', function () {
    $this->assertFileDoesNotExist('example.jpg');
    BrowsershotLambda::html('<h1>Hello world!!</h1>')->save('example.jpg');
    $this->assertFileExists('example.jpg');
});

it('generates pdf from url', function () {
    $this->assertFileDoesNotExist('example.pdf');

    BrowsershotLambda::url('https://example.com')->save('example.pdf');

    $this->assertFileExists('example.pdf');
});

it('generates pdf from html', function () {
    $this->assertFileDoesNotExist('example.pdf');

    BrowsershotLambda::html('<h1>Hello world!!</h1>')->save('example.pdf');

    $this->assertFileExists('example.pdf');
});

it('returns raw html from url', function () {
    $html = BrowsershotLambda::url('https://example.com')->bodyHtml();

    $this->assertStringContainsString('<h1>Example Domain</h1>', $html);
});

it('returns raw html from html', function () {
    $html = BrowsershotLambda::html('<h1>Hello world!!</h1>')->bodyHtml();

    $this->assertEquals("<html><head></head><body><h1>Hello world!!</h1></body></html>\n", $html);
});

it('throws LambdaExecutionException error if browsershot fails', function () {
    BrowsershotLambda::html('<h1>Hello world!!</h1>')
        ->select('#does-not-exist')
        ->bodyHtml();
})->expectException(LambdaExecutionException::class);
