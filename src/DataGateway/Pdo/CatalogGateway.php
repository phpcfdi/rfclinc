<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway\Pdo;

use PDO;
use PhpCfdi\RfcLinc\DataGateway\CatalogGatewayInterface;
use PhpCfdi\RfcLinc\DataGateway\NotFoundException;
use PhpCfdi\RfcLinc\DataGateway\PersistenceException;
use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\VersionDate;

class CatalogGateway extends AbstractPdoGateway implements CatalogGatewayInterface
{
    public function exists(VersionDate $date): bool
    {
        $query = 'select count(*) from catalogs where (version = :version);';
        $count = $this->queryValue($query, ['version' => $date->timestamp()]);
        if (null === $count) {
            throw new PersistenceException('Cannot get count from catalogs');
        }

        return (1 === (int) $count);
    }

    public function get(VersionDate $date): Catalog
    {
        $query = 'select version, records, inserted, updated, deleted from catalogs where (version = :version);';
        $stmt = $this->executePrepared($query, [
            'version' => $date->timestamp(),
        ], 'Cannot get one record from catalogs');
        $values = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $values) {
            throw new NotFoundException("The version {$date->format()} does not exists");
        }

        return $this->createVersionFromArray($date, $values);
    }

    public function latest()
    {
        $query = 'select version, records, inserted, updated, deleted from catalogs order by version desc limit 1;';
        $stmt = $this->executePrepared($query, [], 'Cannot get the latest version');
        $values = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $values) {
            return null;
        }

        return $this->createVersionFromArray(VersionDate::createFromTimestamp((int) $values['version']), $values);
    }

    public function nextAfter(VersionDate $date)
    {
        $query = 'select version, records, inserted, updated, deleted from catalogs'
            . ' where (version > :version) order by version limit 1;';
        $stmt = $this->executePrepared(
            $query,
            ['version' => $date->timestamp()],
            sprintf('Cannot get the version after %s', $date->format())
        );
        $values = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $values) {
            return null;
        }

        return $this->createVersionFromArray(VersionDate::createFromTimestamp((int) $values['version']), $values);
    }

    public function previousBefore(VersionDate $date)
    {
        $query = 'select version, records, inserted, updated, deleted from catalogs'
            . ' where (version < :version) order by version desc limit 1;';
        $stmt = $this->executePrepared(
            $query,
            ['version' => $date->timestamp()],
            sprintf('Cannot get the version before %s', $date->format())
        );
        $values = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $values) {
            return null;
        }

        return $this->createVersionFromArray(VersionDate::createFromTimestamp((int) $values['version']), $values);
    }

    public function insert(Catalog $catalog)
    {
        $query = 'insert into catalogs (version, records, inserted, updated, deleted)'
            . ' values (:version, :records, :inserted, :updated, :deleted);';
        $this->executePrepared($query, [
            'version' => $catalog->date()->timestamp(),
            'records' => $catalog->records(),
            'inserted' => $catalog->inserted(),
            'updated' => $catalog->updated(),
            'deleted' => $catalog->deleted(),
        ], 'Cannot insert into catalogs');
    }

    public function update(Catalog $catalog)
    {
        $query = 'update catalogs set records = :records, inserted = :inserted, updated = :updated, deleted = :deleted'
            . ' where (version = :version);';
        $this->executePrepared($query, [
            'version' => $catalog->date()->timestamp(),
            'records' => $catalog->records(),
            'inserted' => $catalog->inserted(),
            'updated' => $catalog->updated(),
            'deleted' => $catalog->deleted(),
        ], 'Cannot update into catalogs');
    }

    public function delete(VersionDate $date)
    {
        $query = 'delete from catalogs where (version = :version);';
        $this->executePrepared($query, ['version' => $date->timestamp()], 'Cannot delete from catalogs');
    }

    private function createVersionFromArray(VersionDate $date, array $values): Catalog
    {
        return new Catalog(
            $date,
            (int) $values['records'],
            (int) $values['inserted'],
            (int) $values['updated'],
            (int) $values['deleted']
        );
    }
}
