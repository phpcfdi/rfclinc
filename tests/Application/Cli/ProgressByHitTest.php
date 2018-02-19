<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application\Cli;

use EngineWorks\ProgressStatus\Status;
use PhpCfdi\RfcLinc\Application\Cli\ProgressByHit;
use PhpCfdi\RfcLinc\Tests\ProgressObserver;
use PHPUnit\Framework\TestCase;

class ProgressByHitTest extends TestCase
{
    public function testCreateEmpty()
    {
        $progress = new ProgressByHit();
        $this->assertSame(1000, $progress->hits());
    }

    public function testCreateWithValues()
    {
        $status = Status::make();
        $observer = new ProgressObserver();
        $progress = new ProgressByHit($status, [$observer], 1);
        $this->assertSame($status, $progress->getStatus());
        $observers = [];
        foreach ($progress->getObservers() as $item) {
            $observers[] = $item;
        }
        $this->assertSame([$observer], $observers);
        $this->assertSame(1, $progress->hits());
    }

    public function testSetHitInvalidNumberException()
    {
        $progress = new ProgressByHit();

        $this->expectException(\DomainException::class);
        $progress->setHits(-1);
    }

    public function testConstructHitInvalidNumberException()
    {
        $this->expectException(\DomainException::class);
        new ProgressByHit(null, [], -1);
    }

    public function testHitByOne()
    {
        $observer = new ProgressObserver();
        $progress = new ProgressByHit();
        $progress->getObservers()->attach($observer);
        $progress->setHits(1);

        $progress->increase(null, 1);
        $this->assertSame(1, $observer->lastStatus->getValue());
    }

    public function testShouldNotifyChange()
    {
        $first = Status::make(0, '', 1);
        $second = Status::make(0, '', 6);

        $progress = new ProgressByHit();
        $progress->setHits(5);
        $this->assertTrue($progress->shouldNotifyChange($first, $second));

        $progress->setHits(0); // must not notify when hits is zero
        $this->assertFalse($progress->shouldNotifyChange($first, $second));
    }

    public function testHitByFive()
    {
        $observer = new ProgressObserver();
        $progress = new ProgressByHit();
        $progress->getObservers()->attach($observer);
        $progress->setHits(5);

        $progress->increase(null, 1);
        $this->assertSame(0, $observer->lastStatus->getValue(), 'Increase to 1 did not notify');

        $progress->increase(null, 7);
        $this->assertSame(1 + 7, $observer->lastStatus->getValue(), 'The last increase must notify');

        $progress->increase(null, 1);
        $this->assertSame(1 + 7, $observer->lastStatus->getValue(), 'The last increase should not notify');
    }
}
