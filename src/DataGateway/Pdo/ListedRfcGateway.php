<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway\Pdo;

use PDO;
use PhpCfdi\RfcLinc\DataGateway\ListedRfcGatewayInterface;
use PhpCfdi\RfcLinc\DataGateway\NotFoundException;
use PhpCfdi\RfcLinc\DataGateway\PersistenceException;
use PhpCfdi\RfcLinc\Domain\ListedRfc;
use PhpCfdi\RfcLinc\Domain\VersionDate;

class ListedRfcGateway extends AbstractPdoGateway implements ListedRfcGatewayInterface
{
    public function exists(string $rfc): bool
    {
        $query = 'select count(*) from rfcs where (rfc = :rfc);';
        $value = $this->queryValue($query, ['rfc' => $rfc]);
        if (null === $value) {
            throw new PersistenceException('Cannot get count from rfc list');
        }
        return (1 === (int) $value);
    }

    public function get(string $rfc): ListedRfc
    {
        $query = 'select rfc, since, sncf, sub, deleted from rfcs where (rfc = :rfc);';
        $stmt = $this->executePrepared($query, ['rfc' => $rfc], 'Cannot get one record from rfc list');
        $values = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $values) {
            throw new NotFoundException("The RFC $rfc does not exists");
        }
        return $this->createListedRfcFromArray($rfc, $values);
    }

    public function insert(ListedRfc $listedRfc)
    {
        $query = 'insert into rfcs (rfc, since, sncf, sub, deleted)'
            . ' values (:rfc, :since, :sncf, :sub, :deleted);';
        $this->executePrepared($query, [
            'rfc' => $listedRfc->rfc(),
            'since' => $listedRfc->since()->timestamp(),
            'sncf' => (int) $listedRfc->sncf(),
            'sub' => (int) $listedRfc->sub(),
            'deleted' => (int) $listedRfc->deleted(),
        ], 'Cannot insert into rfc list');
    }

    public function update(ListedRfc $listedRfc)
    {
        $query = 'update rfcs set sncf = :sncf, sub = :sub, deleted = :deleted'
            . ' where (rfc = :rfc);';
        $this->executePrepared($query, [
            'rfc' => $listedRfc->rfc(),
            'sncf' => (int) $listedRfc->sncf(),
            'sub' => (int) $listedRfc->sub(),
            'deleted' => (int) $listedRfc->deleted(),
        ], 'Cannot update into rfc list');
    }

    public function delete(string $rfc)
    {
        $query = 'delete from rfcs where (rfc = :rfc);';
        $this->executePrepared($query, ['rfc' => $rfc], 'Cannot delete from rfc list');
    }

    public function markAllAsDeleted()
    {
        $query = 'update rfcs set deleted = :deleted;';
        $this->executePrepared($query, ['deleted' => 1], 'Cannot mark all as deleted on rfc list');
    }

    public function eachDeleted()
    {
        $query = 'select rfc from rfcs where (deleted = :deleted);';
        $stmt = $this->executePrepared($query, ['deleted' => 1], 'Cannot get all deleted from rfc list');
        while (false !== $values = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $values['rfc'];
        }
    }

    public function countDeleted(bool $deleted): int
    {
        $query = 'select count(*) from rfcs where (deleted = :deleted);';
        return (int) $this->queryValue($query, ['deleted' => (int) $deleted]);
    }

    private function createListedRfcFromArray(string $rfc, array $values): ListedRfc
    {
        return new ListedRfc(
            $rfc,
            VersionDate::createFromTimestamp((int) $values['since']),
            (bool) $values['sncf'],
            (bool) $values['sub'],
            (bool) $values['deleted']
        );
    }
}
