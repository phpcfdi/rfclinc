<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway;

use PhpCfdi\RfcLinc\Domain\ListedRfc;

interface ListedRfcGatewayInterface
{
    public function exists(string $rfc): bool;

    public function get(string $rfc): ListedRfc;

    public function insert(ListedRfc $rfc);

    public function update(ListedRfc $listedRfc);

    public function delete(string $rfc);

    public function markAllAsDeleted();

    /**
     * Retrieve a list to be used inside a foreach loop containing a string with an rfc
     * @return \Generator|string[]
     */
    public function eachDeleted();

    public function countDeleted(bool $deleted): int;
}
