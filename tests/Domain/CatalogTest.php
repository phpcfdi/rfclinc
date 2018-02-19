<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Domain;

use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PHPUnit\Framework\TestCase;

class CatalogTest extends TestCase
{
    /** @var VersionDate */
    private $date;

    /** @var Catalog */
    private $catalog;

    protected function setUp()
    {
        $this->date = new VersionDate(2017, 12, 31);
        $this->catalog = new Catalog($this->date, 987654, 10, 20, 30);
    }

    public function testConstruct()
    {
        $this->assertSame($this->date, $this->catalog->date());
        $this->assertSame(987654, $this->catalog->records());
        $this->assertSame(10, $this->catalog->inserted());
        $this->assertSame(20, $this->catalog->updated());
        $this->assertSame(30, $this->catalog->deleted());
    }

    public function testSetCount()
    {
        $this->catalog->setRecords(99);
        $this->assertSame(99, $this->catalog->records());
    }

    public function testSetInserted()
    {
        $this->catalog->setInserted(99);
        $this->assertSame(99, $this->catalog->inserted());
    }

    public function testSetUpdated()
    {
        $this->catalog->setUpdated(99);
        $this->assertSame(99, $this->catalog->updated());
    }

    public function testSetDeleted()
    {
        $this->catalog->setDeleted(99);
        $this->assertSame(99, $this->catalog->deleted());
    }
}
