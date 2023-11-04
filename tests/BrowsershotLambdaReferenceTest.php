<?php

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

it('generates reference hello-world-with-smileys.pdf', function () {
    $this->assertFileDoesNotExist('example.jpg');
    BrowsershotLambda::html('<h1 style="font-weight: 400;">Hello world!! 👋🦫🫠</h1>')->save('example.jpg');
    $this->assertFileExists('example.jpg');
    $this->assertEquals(
        file_get_contents(__DIR__.'/references/hello-world-with-smileys.jpg'),
        file_get_contents('example.jpg'),
    );
});
