<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Updater;

use PhpCfdi\RfcLinc\Tests\TestCase;
use PhpCfdi\RfcLinc\Updater\PackedBlobReader;

class PackedBlobReaderTest extends TestCase
{
    public function testReadAll()
    {
        $source = $this->utilAsset('datasample-signed.txt.gz');
        $reader = new PackedBlobReader();
        $reader->open($source);
        $this->assertTrue($reader->isOpen());
        $lines = [];
        while (false !== $line = $reader->readLine()) {
            $lines[] = $line;
        }
        $reader->close();

        // check read more than 1 line
        $this->assertGreaterThan(0, count($lines), 'The command reader did not read any line');

        // check expected reads
        $this->assertContains('RFC|SNCF|SUBCONTRATACION', $lines);
        $this->assertContains('AOCB7908093IA|0|0', $lines);
        $this->assertContains('EOF', $lines);

        // check expected count
        $expectedCount = 102;
        // local produced files contains this two lines additional to normal text
        if ('Content-Type: text/plain' == $lines[0] && '' == $lines[1]) {
            $expectedCount = $expectedCount + 2;
        }
        $this->assertCount($expectedCount, $lines);
    }
}
