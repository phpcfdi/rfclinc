<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway\Pdo;

use PDO;
use PDOStatement;
use PhpCfdi\RfcLinc\DataGateway\PersistenceException;

abstract class AbstractPdoGateway
{
    /** @var PDO */
    private $pdo;

    /** @var PDOStatement[] */
    private $preparedStatements;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->preparedStatements = [];
    }

    public function preparedStatements(string $query): PDOStatement
    {
        $statement = $this->preparedStatements[$query] ?? null;
        if ($statement instanceof PDOStatement) {
            return $statement;
        }

        $statement = $this->pdo->prepare($query);
        if (false === $statement) {
            throw new \LogicException("Cannot prepare the statement: $query");
        }
        $this->preparedStatements[$query] = $statement;

        return $statement;
    }

    public function executePrepared(string $query, array $arguments = [], string $exceptionMessage = ''): PDOStatement
    {
        $statement = $this->preparedStatements($query);
        if (! $statement->execute($arguments)) {
            $exceptionMessage = $exceptionMessage ? : 'Error retrieving data from database';
            throw new PersistenceException($exceptionMessage);
        }

        return $statement;
    }

    public function queryValue(string $query, array $arguments = [], $defaultValue = null)
    {
        $stmt = $this->executePrepared($query, $arguments);
        $values = $stmt->fetch(PDO::FETCH_NUM);
        if (is_array($values) && array_key_exists(0, $values)) {
            return $values[0];
        }

        return $defaultValue;
    }
}
