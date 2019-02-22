<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests;

use PDO;

class DatabaseTestCase extends TestCase
{
    /** @var PDO|null Use pdo method instead */
    protected static $pdo;

    protected function pdo(): PDO
    {
        if (null === static::$pdo) {
            static::$pdo = $this->createPdo();
            $this->createDatabase(static::$pdo);
        }
        return static::$pdo;
    }

    protected function createPdo(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        // comment to check the pdo exceptions instead of library exceptions
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    protected function createDatabase(PDO $pdo)
    {
        $source = __DIR__ . '/../sql/sqlite/initial.sql';
        if (! file_exists($source)) {
            throw new \LogicException("Missing stucture file $source");
        }
        $pdo->exec(file_get_contents($source));
    }

    protected function cleanupDatabase()
    {
        $queries = [
            'DELETE FROM catalogs;',
            'DELETE FROM rfcs;',
            'DELETE FROM rfclogs;',
        ];
        $this->pdo()->exec(implode("\n", $queries));
    }
}
