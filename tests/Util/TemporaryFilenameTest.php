<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Util;

use PhpCfdi\RfcLinc\Tests\TestCase;
use PhpCfdi\RfcLinc\Util\TemporaryFilename;

class TemporaryFilenameTest extends TestCase
{
    public function testBasicCase()
    {
        $filename = new TemporaryFilename();
        $filenameAsString = (string) $filename;
        $this->assertSame($filenameAsString, $filename->filename());
        $this->assertTrue($filename->deleteOnDestruct());
        $this->assertFileExists($filenameAsString, $filename);
    }

    public function testCreatedWithDirectory()
    {
        $dir = $this->utilAsset('');
        $filename = new TemporaryFilename($dir);
        $this->assertStringStartsWith($dir, $filename->filename());
    }

    public function testCreatedWithPrefix()
    {
        $prefix = 'test-prefix-';
        $filename = new TemporaryFilename('', $prefix);
        $this->assertStringStartsWith($prefix, basename($filename->filename()));
    }

    public function testFileNotExistsAfterRemove()
    {
        $filename = new TemporaryFilename();
        $fileToCkeck = $filename->filename();
        $this->assertFileExists($fileToCkeck);
        unset($filename);
        $this->assertFileNotExists($fileToCkeck);
    }

    public function testAutoRemoveDoesNotBreakWithRemove()
    {
        $filename = new TemporaryFilename();
        $fileToCkeck = $filename->filename();
        $filename->unlink();
        $this->assertFileNotExists($fileToCkeck);
        unset($filename);
        $this->assertFileNotExists($fileToCkeck);
    }

    public function testFileExistsAfterRemoveWithoutAutoRemove()
    {
        $filename = new TemporaryFilename();
        $filename->setDeleteOnDestruct(false);
        $fileToCkeck = $filename->filename();
        $this->assertFileExists($fileToCkeck);
        unset($filename);
        $this->assertFileExists($fileToCkeck);
    }
}
