<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Util;

use PhpCfdi\RfcLinc\Util\FileReader;
use PhpCfdi\RfcLinc\Util\ReaderInterface;

class FileReaderTest extends ReaderTestCase
{
    public static function setUpBeforeClass()
    {
        static::$source = static::utilAsset('datasample-contents.txt');
        static::$expectedReadFile = static::utilAsset('datasample-contents.txt');
    }

    protected function createReader(): ReaderInterface
    {
        return new FileReader();
    }
}
