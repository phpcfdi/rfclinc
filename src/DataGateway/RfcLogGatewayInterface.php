<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway;

use PhpCfdi\RfcLinc\Domain\RfcLog;

interface RfcLogGatewayInterface
{
    /**
     * @param string $rfc
     * @return \Generator|RfcLog[]
     */
    public function eachByRfc(string $rfc);

    public function byRfc(string $rfc): array;

    public function insert(RfcLog $rfcLog);
}
