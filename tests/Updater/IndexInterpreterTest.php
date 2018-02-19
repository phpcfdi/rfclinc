<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Updater;

use PhpCfdi\RfcLinc\Tests\TestCase;
use PhpCfdi\RfcLinc\Updater\IndexInterpreter;

class IndexInterpreterTest extends TestCase
{
    /** @var IndexInterpreter */
    private $indexInterpreter;

    public function setUp()
    {
        parent::setUp();
        $this->indexInterpreter = new IndexInterpreter();
    }

    public function testObtainBlobsWithValidContent()
    {
        $indexfile = $this->utilAsset('list-of-blobs.xml');
        $this->assertFileExists($indexfile);
        $source = file_get_contents($indexfile);
        $blobs = $this->indexInterpreter->obtainBlobs($source);
        $this->assertCount(5, $blobs);
    }

    public function testObtainBlobsWithInvalidXml()
    {
        $source = 'not-valid-xml';

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('valid xml');
        $this->indexInterpreter->obtainBlobs($source);
    }

    public function testObtainBlobsWithNoBlobs()
    {
        $source = '<root><Blobs/></root>';
        $blobs = $this->indexInterpreter->obtainBlobs($source);
        $this->assertCount(0, $blobs);
    }

    public function testBlobFromSimpleXml()
    {
        $source = new \SimpleXMLElement('<Blob />');
        $source->addChild('Name', 'name');
        $source->addChild('Url', 'url');
        $source->addChild('Properties')->addChild('Content-MD5', 'CoXIkGWOiE0/+q7hdBsPDA==');
        $blob = $this->indexInterpreter->blobFromSimpleXml($source);
        $this->assertSame('name', $blob->name());
        $this->assertSame('url', $blob->url());
        $this->assertSame('0a85c890658e884d3ffaaee1741b0f0c', $blob->md5());
    }
}
