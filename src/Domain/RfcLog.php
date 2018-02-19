<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Domain;

class RfcLog
{
    const ACTION_CREATED = 0;

    const ACTION_REMOVED = 1;

    const ACTION_CHANGE_SNCF_ON = 2;

    const ACTION_CHANGE_SNCF_OFF = 3;

    const ACTION_CHANGE_SUB_ON = 4;

    const ACTION_CHANGE_SUB_OFF = 5;

    /** @var VersionDate */
    private $date;

    /** @var string */
    private $rfc;

    /** @var int */
    private $action;

    public function __construct(VersionDate $date, string $rfc, int $action)
    {
        $this->date = $date;
        $this->rfc = $rfc;
        $this->action = $action;
    }

    public function date(): VersionDate
    {
        return $this->date;
    }

    public function rfc(): string
    {
        return $this->rfc;
    }

    public function action(): int
    {
        return $this->action;
    }
}
