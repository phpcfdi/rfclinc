<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway\Pdo;

use PDO;
use PhpCfdi\RfcLinc\DataGateway\OptimizerInterface;

class TransactionOptimizer implements OptimizerInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function prepare()
    {
        $this->pdo->beginTransaction();
    }

    public function finish()
    {
        $this->pdo->commit();
    }
}
