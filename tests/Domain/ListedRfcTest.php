<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Domain;

use PhpCfdi\RfcLinc\Domain\ListedRfc;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PHPUnit\Framework\TestCase;

class ListedRfcTest extends TestCase
{
    /** @var string */
    private $rfc;

    /** @var VersionDate */
    private $since;

    /** @var ListedRfc */
    private $listedRfc;

    protected function setUp()
    {
        $this->rfc = 'COSC8001137NA';
        $this->since = new VersionDate(2017, 12, 31);
        $this->listedRfc = new ListedRfc($this->rfc, $this->since, false, false, false);
    }

    public function testConstruct()
    {
        $this->assertSame($this->rfc, $this->listedRfc->rfc());
        $this->assertSame($this->since, $this->listedRfc->since());
        $this->assertFalse($this->listedRfc->sncf());
        $this->assertFalse($this->listedRfc->sub());
        $this->assertFalse($this->listedRfc->deleted());
    }

    public function testSetSncf()
    {
        $this->listedRfc->setSncf(true);
        $this->assertTrue($this->listedRfc->sncf());
    }

    public function testSetSub()
    {
        $this->listedRfc->setSub(true);
        $this->assertTrue($this->listedRfc->sub());
    }

    public function testSetDeleted()
    {
        $this->listedRfc->setDeleted(true);
        $this->assertTrue($this->listedRfc->deleted());
    }
}
