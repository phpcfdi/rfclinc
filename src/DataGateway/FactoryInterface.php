<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway;

interface FactoryInterface
{
    public function catalog(): CatalogGatewayInterface;

    public function listedRfc(): ListedRfcGatewayInterface;

    public function rfclog(): RfcLogGatewayInterface;

    public function optimizer(): OptimizerInterface;
}
