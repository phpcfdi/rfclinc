<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application\Cli;

use EngineWorks\ProgressStatus\Progress;
use EngineWorks\ProgressStatus\Status;

class ProgressByHit extends Progress
{
    /** @var int */
    private $hits;

    public function __construct(Status $initialStatus = null, array $observers = [], int $hits = 1000)
    {
        parent::__construct($initialStatus, $observers);
        $this->setHits($hits);
    }

    public function hits(): int
    {
        return $this->hits;
    }

    public function setHits(int $hits)
    {
        if ($hits < 0) {
            throw new \DomainException('Expected integer greater or equal than zero');
        }
        $this->hits = $hits;
    }

    public function shouldNotifyChange(Status $currentStatus, Status $newStatus): bool
    {
        $hits = $this->hits();
        if (0 === $hits) {
            return false;
        }
        $current = floor($currentStatus->getValue() / $hits);
        $new = floor($newStatus->getValue() / $hits);
        return ($current !== $new);
    }

    public function getObservers()
    {
        $observers = parent::getObservers();
        return $observers;
    }
}
