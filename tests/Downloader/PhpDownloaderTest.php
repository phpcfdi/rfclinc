<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Downloader;

use PhpCfdi\RfcLinc\Downloader\PhpDownloader;
use PhpCfdi\RfcLinc\Tests\TestCase;

class PhpDownloaderTest extends TestCase
{
    /** @var PhpDownloader */
    private $downloader;

    /** @var string[] */
    private $filesToRemoveAfterTest = [];

    protected function setUp()
    {
        $this->downloader = new PhpDownloader();
    }

    protected function tearDown()
    {
        foreach ($this->filesToRemoveAfterTest as $index => $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testDownload()
    {
        $source = $this->utilAsset('lorem-ipsum.txt');
        $this->assertStringEqualsFile($source, $this->downloader->download($source));
    }

    public function testDownloadAs()
    {
        $source = $this->utilAsset('lorem-ipsum.txt');
        $destination = $this->utilAsset('lorem-ipsum-download.txt');
        $this->filesToRemoveAfterTest[] = $destination;
        $this->downloader->downloadAs($source, $destination);
        $this->assertFileEquals($source, $destination);
    }
}
