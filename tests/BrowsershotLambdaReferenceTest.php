<?php

use Spatie\Pixelmatch\Pixelmatch;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

beforeEach(function () {
    if (file_exists('example.pdf')) {
        unlink('example.pdf');
    }
    if (file_exists('example.png')) {
        unlink('example.png');
    }
});

afterAll(function () {
    if (file_exists('example.pdf')) {
        unlink('example.pdf');
    }
    if (file_exists('example.png')) {
        unlink('example.png');
    }
});

it('generates reference hello-world-with-smileys.png', function () {
    $this->assertFileDoesNotExist('example.png');
    BrowsershotLambda::html('<h1 style="font-weight: 400;">Hello world!! 👋🦫🫠</h1>')->save('example.png');
    $this->assertFileExists('example.png');

    $pixelmatch = Pixelmatch::new(
        __DIR__.'/references/hello-world-with-smileys.png',
        __DIR__.'/../example.png'
    );
    $this->assertTrue($pixelmatch->matches());
});

it('generates reference hello-world-with-smileys.pdf', function () {
    $pdf = BrowsershotLambda::html('<h1 style="font-weight: 400;">Hello world!! 👋🦫🫠</h1>')->pdf();
    $pdfWithCorrectDates = $this->updateCreationDateAndModDateOfPdf($pdf);
    $reference = file_get_contents(__DIR__.'/references/hello-world-with-smileys.pdf');

    $this->assertEquals($reference, $pdfWithCorrectDates);
});
