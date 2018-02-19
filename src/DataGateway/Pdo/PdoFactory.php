<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway\Pdo;

use PDO;
use PhpCfdi\RfcLinc\DataGateway\CatalogGatewayInterface;
use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\DataGateway\ListedRfcGatewayInterface;
use PhpCfdi\RfcLinc\DataGateway\NullOptimizer;
use PhpCfdi\RfcLinc\DataGateway\OptimizerInterface;
use PhpCfdi\RfcLinc\DataGateway\RfcLogGatewayInterface;

class PdoFactory implements FactoryInterface
{
    /** @var PDO */
    private $pdo;

    /** @var OptimizerInterface */
    private $optimizer;

    /** @var CatalogGatewayInterface|null  */
    private $catalog = null;

    /** @var ListedRfcGatewayInterface|null  */
    private $listedRfc = null;

    /** @var RfcLogGatewayInterface */
    private $rfcLog = null;

    public function __construct(PDO $pdo, OptimizerInterface $optimizer = null)
    {
        $this->pdo = $pdo;
        if (null === $optimizer) {
            $optimizer = $this->createOptimizerByDriver($pdo);
        }
        $this->optimizer = $optimizer;
    }

    public function createOptimizerByDriver(PDO $pdo): OptimizerInterface
    {
        $driver = (string) $pdo->getAttribute($pdo::ATTR_DRIVER_NAME);
        if (in_array($driver, ['sqlite', 'pgsql', 'mysql'], true)) {
            return new TransactionOptimizer($pdo);
        }
        return new NullOptimizer();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function catalog(): CatalogGatewayInterface
    {
        if (null === $this->catalog) {
            $this->catalog = new CatalogGateway($this->pdo);
        }
        return $this->catalog;
    }

    public function listedRfc(): ListedRfcGatewayInterface
    {
        if (null === $this->listedRfc) {
            $this->listedRfc = new ListedRfcGateway($this->pdo);
        }
        return $this->listedRfc;
    }

    public function rfclog(): RfcLogGatewayInterface
    {
        if (null === $this->rfcLog) {
            $this->rfcLog = new RfcLogGateway($this->pdo);
        }
        return $this->rfcLog;
    }

    public function optimizer(): OptimizerInterface
    {
        return $this->optimizer;
    }
}
