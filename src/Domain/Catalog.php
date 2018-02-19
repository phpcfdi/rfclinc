<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Domain;

class Catalog
{
    /** @var VersionDate version of the current version */
    private $date;

    /** @var int */
    private $records;

    /** @var int */
    private $inserted;

    /** @var int */
    private $updated;

    /** @var int */
    private $deleted;

    public function __construct(VersionDate $date, int $records, int $inserted, int $updated, int $deleted)
    {
        $this->date = $date;
        $this->records = $records;
        $this->inserted = $inserted;
        $this->updated = $updated;
        $this->deleted = $deleted;
    }

    public function date(): VersionDate
    {
        return $this->date;
    }

    public function records(): int
    {
        return $this->records;
    }

    public function inserted(): int
    {
        return $this->inserted;
    }

    public function updated(): int
    {
        return $this->updated;
    }

    public function deleted(): int
    {
        return $this->deleted;
    }

    public function setRecords(int $records)
    {
        $this->records = $records;
    }

    public function setInserted(int $inserted)
    {
        $this->inserted = $inserted;
    }

    public function setUpdated(int $updated)
    {
        $this->updated = $updated;
    }

    public function setDeleted(int $deleted)
    {
        $this->deleted = $deleted;
    }
}
