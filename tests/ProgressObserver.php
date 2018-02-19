<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests;

use EngineWorks\ProgressStatus\ProgressInterface;
use EngineWorks\ProgressStatus\Status;
use SplObserver;
use SplSubject;

/**
 * This is a minimal implementation of SplObserver that expect to be updated
 * with a ProgressInterface object and store its last value into $lastStatus public property
 *
 * Is used only for testing proposes
 */
class ProgressObserver implements SplObserver
{
    /** @var Status */
    public $lastStatus;

    public function __construct()
    {
        $this->lastStatus = Status::make();
    }

    public function update(SplSubject $subject)
    {
        if ($subject instanceof ProgressInterface) {
            $this->lastStatus = $subject->getStatus();
        }
    }
}
