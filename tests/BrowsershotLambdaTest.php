<?php

use Hammerstone\Sidecar\Exceptions\LambdaExecutionException;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

beforeEach(function () {
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


it('stores screenshot on s3 bucket', function () {
    $this->assertFalse(Storage::disk('s3')->exists('example.jpg'));

    BrowsershotLambda::url('https://example.com')->saveToS3('example.jpg');

    $this->assertTrue(Storage::disk('s3')->exists('example.jpg'));
    Storage::disk('s3')->delete('example.jpg');
    $this->assertFalse(Storage::disk('s3')->exists('example.jpg'));
});

it('stores pdf in s3 bucket', function () {
    $this->assertFalse(Storage::disk('s3')->exists('example.pdf'));

    $etag = BrowsershotLambda::url('https://example.com')->saveToS3('example.pdf');

    $this->assertIsString($etag);

    $this->assertTrue(Storage::disk('s3')->exists('example.pdf'));
    Storage::disk('s3')->delete('example.pdf');
    $this->assertFalse(Storage::disk('s3')->exists('example.pdf'));
});

test('it throws CouldNotTakeBrowsershot Exception if no file extension is passed to saveToS3', function () {
    BrowsershotLambda::html('<h1>Hello world!!</h1>')
        ->saveToS3('example');
})->expectException(CouldNotTakeBrowsershot::class);
