<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\DataGateway\Pdo;

use PhpCfdi\RfcLinc\DataGateway\ListedRfcGatewayInterface;
use PhpCfdi\RfcLinc\DataGateway\NotFoundException;
use PhpCfdi\RfcLinc\DataGateway\Pdo\PdoFactory;
use PhpCfdi\RfcLinc\Domain\ListedRfc;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PhpCfdi\RfcLinc\Tests\DatabaseTestCase;

class ListedRfcGatewayTest extends DatabaseTestCase
{
    /** @var ListedRfcGatewayInterface */
    private $gateway;

    public function setUp()
    {
        $this->gateway = (new PdoFactory($this->pdo()))->listedRfc();
    }

    public function testConstructed()
    {
        $this->assertInstanceOf(ListedRfcGatewayInterface::class, $this->gateway);
    }

    public function testExistsWithNoExistent()
    {
        $rfc = 'XAXX991231001';
        $this->assertFalse($this->gateway->exists($rfc));
    }

    public function testGetWithNoExistent()
    {
        $rfc = 'XAXX991231001';

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($rfc);
        $this->gateway->get($rfc);
    }

    public function testInsert()
    {
        $rfc = 'XAXX991231001';
        $since = new VersionDate(2017, 1, 13);
        $listedRfc = new ListedRfc($rfc, $since);

        $this->gateway->insert($listedRfc);

        $retrieved = $this->gateway->get($rfc);
        $this->assertEquals($listedRfc, $retrieved);
    }

    public function testDoubleInsertFail()
    {
        $rfc = 'XAXX991231002';
        $since = new VersionDate(2017, 1, 13);
        $listedRfc = new ListedRfc($rfc, $since);
        $this->gateway->insert($listedRfc);

        $this->expectException(\RuntimeException::class);
        $this->gateway->insert($listedRfc);
    }

    public function testUpdate()
    {
        $rfc = 'XAXX991231003';
        $since = new VersionDate(2017, 1, 13);
        $listedRfc = new ListedRfc($rfc, $since);
        $this->gateway->insert($listedRfc);

        $listedRfc->setSncf(true);
        $listedRfc->setSub(true);
        $listedRfc->setDeleted(true);

        $this->gateway->update($listedRfc);

        $this->assertEquals($listedRfc, $this->gateway->get($rfc));
    }

    public function testDelete()
    {
        $rfc = 'XAXX991231003';
        $since = new VersionDate(2017, 1, 13);
        $listedRfc = new ListedRfc($rfc, $since);

        // remove if previous
        $this->gateway->delete($rfc);

        // insert and check it must not exist
        $this->gateway->insert($listedRfc);
        $this->assertTrue($this->gateway->exists($rfc));

        // remove and check it must not exist
        $this->gateway->delete($rfc);
        $this->assertFalse($this->gateway->exists($rfc));
    }
}
