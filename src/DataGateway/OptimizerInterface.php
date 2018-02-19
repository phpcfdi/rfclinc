<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway;

interface OptimizerInterface
{
    public function prepare();

    public function finish();
}
