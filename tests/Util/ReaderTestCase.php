<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Util;

use PhpCfdi\RfcLinc\Tests\TestCase;
use PhpCfdi\RfcLinc\Util\ReaderInterface;

abstract class ReaderTestCase extends TestCase
{
    /** @var string */
    protected static $source = '';

    /** @var string */
    protected static $expectedReadFile = '';

    /** @var ReaderInterface */
    protected $reader;

    abstract protected function createReader(): ReaderInterface;

    protected function source(): string
    {
        return static::$source;
    }

    protected function setUp()
    {
        parent::setUp();
        if ('' === $this->source()) {
            $this->markTestIncomplete('Must setup test source property');
        }
        $this->reader = $this->createReader();
    }

    public function testOpenAndClose()
    {
        $this->reader->open($this->source());
        $this->assertTrue($this->reader->isOpen());
        $this->reader->close();
        $this->assertFalse($this->reader->isOpen());
    }

    public function testReadAll()
    {
        $this->assertFileExists(static::$expectedReadFile, 'Must setup the test with the $expectedReadFile property');

        $this->reader->open($this->source());
        $buffer = [];
        while (false !== $line = $this->reader->readLine()) {
            $buffer[] = $line;
        }
        $this->reader->close();

        $this->assertStringEqualsFile(static::$expectedReadFile, implode(PHP_EOL, $buffer) . PHP_EOL);
    }

    public function testCloseDoesNotThrowExceptionWhenIsNotOpen()
    {
        $this->reader->close();
        $this->assertFalse($this->reader->isOpen());
    }

    public function testThrowExceptionIfReadWhenNotOpen()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not open');

        $this->reader->readLine();
    }
}
