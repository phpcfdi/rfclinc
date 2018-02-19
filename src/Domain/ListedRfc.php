<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Domain;

class ListedRfc
{
    /** @var string Value of "rfc" */
    private $rfc;

    /** @var VersionDate since when the record exists */
    private $since;

    /** @var bool Value of "sncf" */
    private $sncf;

    /** @var bool Value of "subcontratación" */
    private $sub;

    /** @var bool current state if the rfc is removed from the catalog */
    private $deleted;

    public function __construct(
        string $rfc,
        VersionDate $since,
        bool $sncf = false,
        bool $sub = false,
        bool $deleted = false
    ) {
        $this->rfc = $rfc;
        $this->since = $since;
        $this->sncf = $sncf;
        $this->sub = $sub;
        $this->deleted = $deleted;
    }

    public function rfc(): string
    {
        return $this->rfc;
    }

    public function since(): VersionDate
    {
        return $this->since;
    }

    public function sncf(): bool
    {
        return $this->sncf;
    }

    public function sub(): bool
    {
        return $this->sub;
    }

    public function deleted(): bool
    {
        return $this->deleted;
    }

    public function setSncf(bool $sncf)
    {
        $this->sncf = $sncf;
    }

    public function setSub(bool $sub)
    {
        $this->sub = $sub;
    }

    public function setDeleted(bool $deleted)
    {
        $this->deleted = $deleted;
    }
}
