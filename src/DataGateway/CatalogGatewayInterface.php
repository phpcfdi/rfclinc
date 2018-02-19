<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\DataGateway;

use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\VersionDate;

interface CatalogGatewayInterface
{
    public function exists(VersionDate $date): bool;

    public function get(VersionDate $date): Catalog;

    /** @return Catalog|null */
    public function latest();

    /**
     * @param VersionDate $date
     * @return Catalog|null
     */
    public function nextAfter(VersionDate $date);

    /**
     * @param VersionDate $date
     * @return Catalog|null
     */
    public function previousBefore(VersionDate $date);

    public function insert(Catalog $version);

    public function update(Catalog $version);

    public function delete(VersionDate $version);
}
