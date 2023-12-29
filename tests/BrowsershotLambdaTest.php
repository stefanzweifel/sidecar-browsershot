<?php

use Hammerstone\Sidecar\Exceptions\LambdaExecutionException;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
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
    BrowsershotLambda::html('<h1>Hello world!! 👋🦫🫠</h1>')->save('example.jpg');
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

    $this->assertEquals('<html><head></head><body><h1>Hello world!!</h1></body></html>', $html);
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

it('returns a trimed base64pdf', function () {
    $base64 = BrowsershotLambda::html('<h1>Hello world!!</h1>')
        ->base64pdf();

    $decode = base64_decode($base64);

    $this->assertEquals($base64, base64_encode($decode));
});

it('reads a file from an s3 bucket', function () {
    // Create test file in s3 bucket
    Storage::disk('s3')->put('example.html', '<h1>Hello world!!</h1>');
    $this->assertTrue(Storage::disk('s3')->exists('example.html'));

    BrowsershotLambda::readHtmlFromS3('example.html')->save('example.pdf');

    $this->assertFileExists('example.pdf');
});

it('applies image manipulations when calling save method', function () {
    $this->assertFileDoesNotExist('example.jpg');

    BrowsershotLambda::url('https://example.com')
        ->windowSize(1920, 1080)
        ->fit(Manipulations::FIT_CONTAIN, 200, 200)
        ->save('example.jpg');

    $image = new Image('example.jpg');
    $this->assertEquals(200, $image->getWidth());
});

it('applies image manipulations when calling saveToS3 method', function () {
    $this->assertFalse(Storage::disk('s3')->exists('example.jpg'));

    // Create screenshot from example.com and resize it to 200x200
    BrowsershotLambda::url('https://example.com')
        ->windowSize(1920, 1080)
        ->fit(Manipulations::FIT_CONTAIN, 200, 200)
        ->saveToS3('example.jpg');

    $this->assertTrue(Storage::disk('s3')->exists('example.jpg'));

    // Download file from s3 bucket to local disc
    Storage::disk('local')->put('example.jpg', Storage::disk('s3')->get('example.jpg'));
    $path = Storage::disk('local')->path('example.jpg');

    // Check image dimensions of local copy.
    $image = new Image($path);
    $this->assertEquals(200, $image->getWidth());

    // Delete file from S3 and local disc
    Storage::disk('local')->delete('example.jpg');
    Storage::disk('s3')->delete('example.jpg');
    $this->assertFalse(Storage::disk('s3')->exists('example.jpg'));
});
