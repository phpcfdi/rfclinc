<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway;

class NullOptimizer implements OptimizerInterface
{
    public function prepare()
    {
    }

    public function finish()
    {
    }
}
