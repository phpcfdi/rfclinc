<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Updater;

use EngineWorks\ProgressStatus\NullProgress;
use EngineWorks\ProgressStatus\ProgressInterface;
use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\ListedRfc;
use PhpCfdi\RfcLinc\Domain\RfcLog;
use PhpCfdi\RfcLinc\Util\ReaderInterface;

class Importer
{
    /** @var FactoryInterface */
    private $gateways;

    /** @var Catalog */
    private $catalog;

    /** @var ProgressInterface */
    private $progress;

    public function __construct(Catalog $catalog, FactoryInterface $gateways, ProgressInterface $progress = null)
    {
        $this->catalog = $catalog;
        $this->gateways = $gateways;
        $this->progress = $progress ?: new NullProgress();
    }

    public function catalog(): Catalog
    {
        return $this->catalog;
    }

    public function gateways(): FactoryInterface
    {
        return $this->gateways;
    }

    public function progress(): ProgressInterface
    {
        return $this->progress;
    }

    public function incrementInserted()
    {
        $this->catalog->setInserted($this->catalog->inserted() + 1);
    }

    public function incrementUpdated()
    {
        $this->catalog->setUpdated($this->catalog->updated() + 1);
    }

    public function incrementDeleted()
    {
        $this->catalog->setDeleted($this->catalog->deleted() + 1);
    }

    public function importReader(ReaderInterface $reader): int
    {
        $processedLines = 0;
        while (true) {
            $line = $reader->readLine();
            if (false === $line) { // line: end of line
                break;
            }
            if ($this->importLine($line)) {
                $processedLines = $processedLines + 1;
                $this->progress->increase();
            }
        }
        return $processedLines;
    }

    public function importLine(string $line): bool
    {
        $input = str_getcsv($line, '|');
        if (3 === count($input)) {
            // do a simple check of the last item (can only be 1 or 0)
            // todo: benchmark to test using in_array
            if ('0' === $input[2] || '1' === $input[2]) {
                $this->importRecord($input[0], '1' === $input[1], '1' === $input[2]);
                return true;
            }
        }
        return false;
    }

    public function importRecord(string $rfc, bool $sncf, bool $sub)
    {
        $gwRfc = $this->gateways->listedRfc();
        /*
         * This has been tested
         * Is less expensive to perform exists + get + update than get + update
         * in mysql and pgsql, sqlite is almost the same
         */
        if ($gwRfc->exists($rfc)) {
            // update
            $this->performUpdate($gwRfc->get($rfc), $sncf, $sub);
        } else {
            // insert
            $this->performInsert(new ListedRfc($rfc, $this->catalog->date(), $sncf, $sub, false));
        }
    }

    public function performInsert(ListedRfc $listedRfc)
    {
        $this->gateways->listedRfc()->insert($listedRfc);
        $this->createLog($listedRfc->rfc(), RfcLog::ACTION_CREATED);
        $this->incrementInserted();
    }

    public function performUpdate(ListedRfc $listedRfc, bool $sncf, bool $sub)
    {
        $changed = false;
        if ($sncf !== $listedRfc->sncf()) {
            $listedRfc->setSncf($sncf);
            $this->createLog($listedRfc->rfc(), $sncf ? RfcLog::ACTION_CHANGE_SNCF_ON : RfcLog::ACTION_CHANGE_SNCF_OFF);
            $changed = true;
        }
        if ($sub !== $listedRfc->sub()) {
            $listedRfc->setSub($sub);
            $this->createLog($listedRfc->rfc(), $sub ? RfcLog::ACTION_CHANGE_SUB_ON : RfcLog::ACTION_CHANGE_SUB_OFF);
            $changed = true;
        }

        // change delete status and store
        $listedRfc->setDeleted(false);
        $this->gateways->listedRfc()->update($listedRfc);

        // only increment counter if a significant value changed
        if ($changed) {
            $this->incrementUpdated();
        }
    }

    public function performDelete(string $rfc)
    {
        $this->createLog($rfc, RfcLog::ACTION_REMOVED);
        $this->incrementDeleted();
    }

    public function createLog(string $rfc, int $type)
    {
        $this->gateways->rfclog()->insert(
            new RfcLog($this->catalog->date(), $rfc, $type)
        );
    }
}
