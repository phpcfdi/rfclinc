<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application\Providers;

use PhpCfdi\RfcLinc\Application\Config;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /** @return string[] */
    private $configFiles;

    public function __construct(string $configFile = '')
    {
        if ('' === $configFile) {
            $this->configFiles = $this->defaultConfigFiles();
        } else {
            $this->configFiles = [$configFile];
        }
    }

    public function register(Container $container)
    {
        $container['config'] = function (Container $container) {
            foreach ($this->configFiles() as $file) {
                $config = $this->tryCreateConfigFromFile($file);
                if ($config instanceof Config) {
                    $container['debug'] = ! $config->isEnvironmentProduction();
                    return $config;
                }
            }
            throw new \RuntimeException('Cannot locate any valid config file');
        };
    }

    /** @return string[] */
    public function configFiles(): array
    {
        return $this->configFiles;
    }

    /** @return string[] */
    public function defaultConfigFiles(): array
    {
        return [
            getcwd() . '/.rfcLinc.php',
            getcwd() . 'rfcLinc.config.php',
            // __DIR__ is src/Application/Providers
            dirname(__DIR__, 3) . '/config/config.php',
        ];
    }

    /**
     * @param string $filename
     * @return Config|null
     */
    public function tryCreateConfigFromFile(string $filename)
    {
        try {
            return Config::createFromConfigFile($filename);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
