<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application\Providers;

use PhpCfdi\RfcLinc\Application\Config;
use PhpCfdi\RfcLinc\Application\Providers\ConfigServiceProvider;
use PhpCfdi\RfcLinc\Tests\TestCase;
use Pimple\Container;

class ConfigServiceProviderTest extends TestCase
{
    public function testConstructWithArgument()
    {
        $service = new ConfigServiceProvider('foo');
        $files = $service->configFiles();

        $this->assertEquals(['foo'], $files);
    }

    public function testConstructWithoutArgument()
    {
        $service = new ConfigServiceProvider();
        $files = $service->configFiles();

        $this->assertGreaterThan(0, count($files));
        $this->assertEquals($service->defaultConfigFiles(), $files);
    }

    public function testCreateConfigWithValidFile()
    {
        $service = new ConfigServiceProvider();
        $file = $this->utilAsset('config-sample.php');

        $this->assertInstanceOf(Config::class, $service->tryCreateConfigFromFile($file));
    }

    public function testCreateConfigWithInvalidFile()
    {
        $service = new ConfigServiceProvider();

        $this->assertNull($service->tryCreateConfigFromFile('foo'));
    }

    public function testRegisterWithValidConfigFile()
    {
        $file = $this->utilAsset('config-sample.php');

        $container = new Container();
        $service = new ConfigServiceProvider($file); // avoid load default config files

        $this->assertArrayNotHasKey('config', $container);
        $service->register($container);
        $this->assertArrayHasKey('config', $container);

        $this->assertInstanceOf(Config::class, $container['config']);
    }

    public function testRegisterWithInvalidConfigFile()
    {
        $file = 'foo';

        $container = new Container();
        $service = new ConfigServiceProvider($file); // avoid load default config files

        $service->register($container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot locate any valid config file');

        $container->offsetGet('config');
    }
}
