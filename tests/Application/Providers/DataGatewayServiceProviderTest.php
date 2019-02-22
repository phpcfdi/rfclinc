<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application\Providers;

use PhpCfdi\RfcLinc\Application\Config;
use PhpCfdi\RfcLinc\Application\Providers\DataGatewayServiceProvider;
use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\Tests\TestCase;
use Pimple\Container;

class DataGatewayServiceProviderTest extends TestCase
{
    public function testCreatePdoWithNoDsn()
    {
        $service = new DataGatewayServiceProvider();
        $config = Config::createFromArray([]);

        $this->expectExceptionMessage('No database DSN is configured');
        $service->createPdo($config);
    }

    public function testCreatePdoWithInvalidDsn()
    {
        $service = new DataGatewayServiceProvider();
        $config = Config::createFromArray([Config::KEY_DB_DSN => 'foo']);

        $this->expectExceptionMessage('Unable to create PDO');
        $service->createPdo($config);
    }

    public function testCreatePdoWithValidDsn()
    {
        $service = new DataGatewayServiceProvider();
        $config = Config::createFromArray([Config::KEY_DB_DSN => 'sqlite::memory:']);

        $this->assertNotNull($service->createPdo($config));
    }

    public function testRegister()
    {
        $container = new Container();
        $config = Config::createFromArray([Config::KEY_DB_DSN => 'sqlite::memory:']);
        $container['config'] = $config;
        $service = new DataGatewayServiceProvider();

        $container->register($service);
        $this->assertArrayHasKey('gateways', $container);

        $this->assertInstanceOf(FactoryInterface::class, $container['gateways']);
    }
}
