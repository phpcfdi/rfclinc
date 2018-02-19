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
    public function testCreatePdoWithNoDns()
    {
        $service = new DataGatewayServiceProvider();
        $config = Config::createFromArray([]);

        $this->expectExceptionMessage('No database DNS is configured');
        $service->createPdo($config);
    }

    public function testCreatePdoWithInvalidDns()
    {
        $service = new DataGatewayServiceProvider();
        $config = Config::createFromArray([Config::KEY_DB_DNS => 'foo']);

        $this->expectExceptionMessage('Unable to create PDO');
        $service->createPdo($config);
    }

    public function testCreatePdoWithValidDns()
    {
        $service = new DataGatewayServiceProvider();
        $config = Config::createFromArray([Config::KEY_DB_DNS => 'sqlite::memory:']);

        $this->assertNotNull($service->createPdo($config));
    }

    public function testRegister()
    {
        $container = new Container();
        $config = Config::createFromArray([Config::KEY_DB_DNS => 'sqlite::memory:']);
        $container['config'] = $config;
        $service = new DataGatewayServiceProvider();

        $container->register($service);
        $this->assertArrayHasKey('gateways', $container);

        $this->assertInstanceOf(FactoryInterface::class, $container['gateways']);
    }
}
