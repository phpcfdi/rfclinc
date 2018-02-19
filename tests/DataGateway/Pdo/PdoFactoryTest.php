<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\DataGateway\Pdo;

use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\DataGateway\NullOptimizer;
use PhpCfdi\RfcLinc\DataGateway\Pdo\PdoFactory;
use PhpCfdi\RfcLinc\Tests\DatabaseTestCase;

class PdoFactoryTest extends DatabaseTestCase
{
    /** @var PdoFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new PdoFactory($this->pdo());
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(FactoryInterface::class, $this->factory);
        $this->assertSame($this->pdo(), $this->factory->pdo());
    }

    public function testConstructWithOptimizer()
    {
        $optimizer = new NullOptimizer();
        $factory = new PdoFactory($this->pdo(), $optimizer);
        $this->assertSame($optimizer, $factory->optimizer());
    }

    public function testVersionReturnsTheSameObject()
    {
        $first = $this->factory->catalog();
        $second = $this->factory->catalog();

        $this->assertSame($first, $second);
    }
}
