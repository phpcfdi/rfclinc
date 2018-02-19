<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Util;

use PhpCfdi\RfcLinc\Util\CommandReader;
use PhpCfdi\RfcLinc\Util\ReaderInterface;

class CommandReaderTest extends ReaderTestCase
{
    public static function setUpBeforeClass()
    {
        static::$source = 'cat ' . escapeshellarg(static::utilAsset('datasample-contents.txt'));
        static::$expectedReadFile = static::utilAsset('datasample-contents.txt');
    }

    protected function createReader(): ReaderInterface
    {
        return new CommandReader();
    }

    public function testWithInfiniteRunningCommand()
    {
        $reader = new CommandReader();

        $reader->open('ping 127.0.0.1');
        $this->assertTrue($reader->isOpen());

        $reads = 2;
        for ($i = 0; $i < $reads; $i++) {
            $readLine = $reader->readLine();
            $this->assertNotSame(false, $readLine);
            $this->assertNotEmpty($readLine);
        }
        $reader->close();
        $this->assertFalse($reader->isOpen());
    }
}
