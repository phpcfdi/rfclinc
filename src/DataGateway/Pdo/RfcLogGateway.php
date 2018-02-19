<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway\Pdo;

use PDO;
use PDOStatement;
use PhpCfdi\RfcLinc\DataGateway\RfcLogGatewayInterface;
use PhpCfdi\RfcLinc\Domain\RfcLog;
use PhpCfdi\RfcLinc\Domain\VersionDate;

class RfcLogGateway extends AbstractPdoGateway implements RfcLogGatewayInterface
{
    public function eachByRfc(string $rfc)
    {
        $stmt = $this->openStatementByRfc($rfc);
        while (false !== $values = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $this->createRfcLogFromArray($values);
        }
    }

    public function byRfc(string $rfc): array
    {
        $list = [];
        $stmt = $this->openStatementByRfc($rfc);
        while (false !== $values = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = $this->createRfcLogFromArray($values);
        }
        return $list;
    }

    public function insert(RfcLog $rfcLog)
    {
        $query = 'insert into rfclogs (version, rfc, action)'
            . ' values (:version, :rfc, :action);';
        $this->executePrepared($query, [
            'version' => $rfcLog->date()->timestamp(),
            'rfc' => $rfcLog->rfc(),
            'action' => $rfcLog->action(),
        ], 'Cannot insert into rfc logs list');
    }

    private function openStatementByRfc(string $rfc): PDOStatement
    {
        $query = 'select version, rfc, action from rfclogs'
            . ' where (rfc = :rfc) order by version';
        return $this->executePrepared($query, ['rfc' => $rfc], 'Cannot get logs from rfc list');
    }

    private function createRfcLogFromArray(array $values): RfcLog
    {
        return new RfcLog(
            VersionDate::createFromTimestamp((int) $values['version']),
            (string) $values['rfc'],
            (int) $values['action']
        );
    }
}
