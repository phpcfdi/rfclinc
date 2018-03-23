<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\DataGateway\Pdo;

use PhpCfdi\RfcLinc\DataGateway\CatalogGatewayInterface;
use PhpCfdi\RfcLinc\DataGateway\NotFoundException;
use PhpCfdi\RfcLinc\DataGateway\Pdo\PdoFactory;
use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PhpCfdi\RfcLinc\Tests\DatabaseTestCase;

class CatalogGatewayTest extends DatabaseTestCase
{
    /** @var CatalogGatewayInterface */
    private $gateway;

    public function setUp()
    {
        $this->gateway = (new PdoFactory($this->pdo()))->catalog();
    }

    public function testConstructed()
    {
        $this->assertInstanceOf(CatalogGatewayInterface::class, $this->gateway);
    }

    public function testExistsWithNoExistent()
    {
        $date = VersionDate::createFromString('2015-01-05');
        $this->assertFalse($this->gateway->exists($date));
    }

    public function testGetWithNoExistent()
    {
        $date = VersionDate::createFromString('2015-01-10');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('2015-01-10');
        $this->gateway->get($date);
    }

    public function testInsert()
    {
        $date = VersionDate::createFromString('2015-01-13');
        $catalog = new Catalog($date, 1000, 10, 20, 30);

        $this->gateway->insert($catalog);

        $retrieved = $this->gateway->get($date);
        $this->assertEquals($catalog, $retrieved);
    }

    public function testDoubleInsertFail()
    {
        $date = VersionDate::createFromString('2015-01-20');
        $catalog = new Catalog($date, 1000, 10, 20, 30);
        $this->gateway->insert($catalog);

        $this->expectException(\RuntimeException::class);
        $this->gateway->insert($catalog);
    }

    public function testUpdate()
    {
        $date = VersionDate::createFromString('2015-01-15');
        $catalog = new Catalog($date, 1000, 10, 20, 30);
        $this->gateway->insert($catalog);

        $catalog->setRecords(9999);
        $catalog->setInserted(11);
        $catalog->setUpdated(22);
        $catalog->setDeleted(33);

        $this->gateway->update($catalog);

        $this->assertEquals($catalog, $this->gateway->get($date));
    }

    public function testDelete()
    {
        $date = VersionDate::createFromString('2015-01-20');
        $catalog = new Catalog($date, 1000, 10, 20, 30);
        // remove if previous
        $this->gateway->delete($date);

        // insert and check it must not exist
        $this->gateway->insert($catalog);
        $this->assertTrue($this->gateway->exists($date));

        // remove and check it must not exist
        $this->gateway->delete($date);
        $this->assertFalse($this->gateway->exists($date));
    }

    public function testPreviousAndNext()
    {
        $this->cleanupDatabase();

        $first = new Catalog(VersionDate::createFromString('2018-01-01'), 1000, 10, 20, 30);
        $second = new Catalog(VersionDate::createFromString('2018-01-02'), 1000, 15, 25, 35);
        $third = new Catalog(VersionDate::createFromString('2018-01-03'), 1000, 17, 27, 37);

        $this->gateway->insert($first);
        $this->gateway->insert($second);
        $this->gateway->insert($third);

        // latest
        $this->assertEquals($third, $this->gateway->latest());

        // before
        $this->assertEquals($second, $this->gateway->previousBefore($third->date()));
        $this->assertEquals($first, $this->gateway->previousBefore($second->date()));
        $this->assertNull($this->gateway->previousBefore($first->date()));

        // after
        $this->assertEquals($second, $this->gateway->nextAfter($first->date()));
        $this->assertEquals($third, $this->gateway->nextAfter($second->date()));
        $this->assertNull($this->gateway->nextAfter($third->date()));
    }
}
