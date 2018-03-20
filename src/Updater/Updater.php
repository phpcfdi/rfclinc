<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Updater;

use EngineWorks\ProgressStatus\NullProgress;
use EngineWorks\ProgressStatus\ProgressInterface;
use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PhpCfdi\RfcLinc\Downloader\DownloaderInterface;
use PhpCfdi\RfcLinc\Downloader\PhpDownloader;
use PhpCfdi\RfcLinc\Util\ReaderInterface;
use PhpCfdi\RfcLinc\Util\TemporaryFilename;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Updater
{
    const URL_BLOBS_LIST = 'https://cfdisat.blob.core.windows.net/lco?restype=container&comp=list';

    /** @var VersionDate */
    private $date;

    /** @var FactoryInterface */
    private $gateways;

    /** @var DownloaderInterface */
    private $downloader;

    /** @var IndexInterpreter */
    private $indexInterpreter;

    /** @var Importer|null */
    private $importer;

    /** @var LoggerInterface */
    private $logger;

    /** @var ProgressInterface */
    private $progress;

    public function __construct(VersionDate $date, FactoryInterface $gateways)
    {
        $this->date = $date;
        $this->gateways = $gateways;
        $this->downloader = new PhpDownloader();
        $this->indexInterpreter = new IndexInterpreter();
        $this->logger = new NullLogger();
        $this->progress = new NullProgress();
    }

    public function hasImporter(): bool
    {
        return ($this->importer instanceof Importer);
    }

    public function importer(): Importer
    {
        if ($this->importer instanceof Importer) {
            return $this->importer;
        }
        throw new \LogicException('There is no importer, did you call run() method?');
    }

    public function version(): Catalog
    {
        return $this->importer()->catalog();
    }

    public function progress(): ProgressInterface
    {
        return $this->progress;
    }

    public function date(): VersionDate
    {
        return $this->date;
    }

    public function gateways(): FactoryInterface
    {
        return $this->gateways;
    }

    public function downloader(): DownloaderInterface
    {
        return $this->downloader;
    }

    public function indexInterpreter(): IndexInterpreter
    {
        return $this->indexInterpreter;
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setDownloader(DownloaderInterface $downloader)
    {
        $this->downloader = $downloader;
    }

    public function setIndexInterpreter(IndexInterpreter $indexInterpreter)
    {
        $this->indexInterpreter = $indexInterpreter;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setProgress(ProgressInterface $progress)
    {
        $this->progress = $progress;
    }

    public function run(): int
    {
        $indexUrl = $this->indexUrl();
        $this->logger->info("Processing {$indexUrl}...");

        $this->logger->debug("Downloading {$indexUrl}...");
        $indexContents = $this->downloader->download($indexUrl);

        $this->logger->debug('Obtaining blobs...');
        $blobs = $this->indexInterpreter->obtainBlobs($indexContents);
        $blobsCount = count($blobs);

        $this->logger->debug("Processing $blobsCount blobs...");
        $processedLines = $this->runBlobs(...$blobs);

        $this->logger->info(sprintf('Processed %s lines', number_format($processedLines)));
        return $processedLines;
    }

    public function runBlobs(Blob ...$blobs): int
    {
        $processedLines = 0;
        $this->processBegin();
        foreach ($blobs as $blob) {
            $processedLines = $processedLines + $this->processBlob($blob);
        }
        $this->processEnd();

        return $processedLines;
    }

    public function processBegin()
    {
        $this->logger->notice('Starting general process...');

        // obtain or create version
        $gwCatalogs = $this->gateways->catalog();
        if ($gwCatalogs->exists($this->date)) {
            throw new \RuntimeException('The version is already in the catalog, it was not expected to exists');
        }
        // start optimizations
        $this->gateways->optimizer()->prepare();

        // create and store version
        $catalog = new Catalog($this->date, 0, 0, 0, 0);
        $gwCatalogs->insert($catalog);

        // create importer
        $this->importer = new Importer($catalog, $this->gateways, $this->progress());

        // set all records as deleted
        $this->gateways->listedRfc()->markAllAsDeleted();

        $this->logger->debug('General process started');
    }

    public function processBlob(Blob $blob): int
    {
        // create temp file
        $downloaded = new TemporaryFilename();
        $filename = (string) $downloaded;
        $url = $blob->url();
        $expectedMd5 = $blob->md5();

        $this->logger->info("Downloading $url...");

        // download the resourse
        $this->logger->debug("Downloading $url into $filename...");
        $downloadStart = time();
        $this->downloader->downloadAs($url, $filename);
        $downloadElapsed = time() - $downloadStart;
        $this->logger->debug("Download $url takes $downloadElapsed seconds");

        // check the md5 checksum
        if ('' !== $expectedMd5) {
            $this->logger->debug("Checking $expectedMd5 on $filename...");
            $this->checkFileMd5($filename, $expectedMd5);
        }

        // process file
        $this->logger->debug("Opening $filename (as packed data)...");
        $reader = $this->createReaderForPackedFile($filename);
        $processedLines = $this->processReader($reader);
        $this->logger->debug("Closing $filename...");
        $reader->close();

        $this->logger->notice(sprintf('Blob %s process %s lines', $url, number_format($processedLines)));

        // clear the resource
        $this->logger->debug("Removing $filename...");
        $downloaded->unlink();

        return $processedLines;
    }

    public function processReader(ReaderInterface $reader): int
    {
        return $this->importer()->importReader($reader);
    }

    public function processEnd()
    {
        $importer = $this->importer();
        $catalog = $importer->catalog();
        $gwRfc = $this->gateways->listedRfc();

        // count how many were deleted and log
        $this->logger->debug('Checking deletes...');
        foreach ($gwRfc->eachDeleted() as $rfc) {
            $importer->performDelete($rfc);
        }
        $this->logger->debug(sprintf('Found %s lines deleted', number_format($catalog->deleted())));

        $active = $gwRfc->countDeleted(false);
        $catalog->setRecords($active);
        $this->logger->info(sprintf('Found %s RFC active', number_format($active)));

        // store current version
        $this->logger->debug('Saving version...');
        $this->gateways->catalog()->update($catalog);

        // end optimizations
        $this->gateways->optimizer()->finish();
        $this->logger->notice('General process finish');
    }

    public function checkFileMd5(string $filename, string $expectedMd5)
    {
        // check md5
        $md5file = (string) md5_file($filename);
        if ($md5file !== $expectedMd5) {
            throw new \RuntimeException(sprintf(
                'The MD5 from file "%s" does not match with "%s"',
                $md5file,
                $expectedMd5
            ));
        }
    }

    public function createReaderForPackedFile(string $filename): ReaderInterface
    {
        $reader = new PackedBlobReader();
        $reader->open($filename);
        return $reader;
    }

    public function indexUrl(): string
    {
        return $this->buildIndexUrl($this->date);
    }

    public static function buildIndexUrl(VersionDate $date): string
    {
        return static::URL_BLOBS_LIST . '&prefix=l_RFC_' . $date->format('_');
    }
}
