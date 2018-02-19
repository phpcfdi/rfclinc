<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Updater;

use PhpCfdi\RfcLinc\Updater\Blob;
use PHPUnit\Framework\TestCase;

class BlobTest extends TestCase
{
    public function testConstruct()
    {
        $blob = new Blob('name', 'url', 'CoXIkGWOiE0/+q7hdBsPDA==');
        $this->assertSame('name', $blob->name());
        $this->assertSame('url', $blob->url());
        $this->assertSame('CoXIkGWOiE0/+q7hdBsPDA==', $blob->contentMd5());
        $this->assertSame('0a85c890658e884d3ffaaee1741b0f0c', $blob->md5());
    }

    public function testMd5Valid()
    {
        $inputBase64 = 'CoXIkGWOiE0/+q7hdBsPDA==';
        $expectedMd5 = '0a85c890658e884d3ffaaee1741b0f0c';
        $this->assertSame($expectedMd5, Blob::convertMd5BlobToMd5Standard($inputBase64));
    }

    public function testMd5NotValid()
    {
        $inputBase64 = 'C00000000E0/+q7hdBsPDA==';
        $expectedMd5 = '0a85c890658e884d3ffaaee1741b0f0c';
        $this->assertNotEquals($expectedMd5, Blob::convertMd5BlobToMd5Standard($inputBase64));
    }

    public function testMd5Empty()
    {
        $this->assertSame('', Blob::convertMd5BlobToMd5Standard(''));
    }

    public function testMd5NotBase64()
    {
        $this->assertSame('', Blob::convertMd5BlobToMd5Standard('Ñ'));
    }
}
