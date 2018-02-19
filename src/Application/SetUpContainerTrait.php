<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application;

use PhpCfdi\RfcLinc\Application\Providers\ConfigServiceProvider;
use PhpCfdi\RfcLinc\Application\Providers\DataGatewayServiceProvider;
use Pimple\Container;

trait SetUpContainerTrait
{
    public static function setUpContainer(Container $container)
    {
        // services
        $container->register(new ConfigServiceProvider());
        $container->register(new DataGatewayServiceProvider());
    }
}
