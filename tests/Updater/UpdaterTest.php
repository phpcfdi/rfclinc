<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Updater;

use EngineWorks\ProgressStatus\NullProgress;
use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\DataGateway\Pdo\PdoFactory;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PhpCfdi\RfcLinc\Downloader\DownloaderInterface;
use PhpCfdi\RfcLinc\Tests\DatabaseTestCase;
use PhpCfdi\RfcLinc\Updater\Blob;
use PhpCfdi\RfcLinc\Updater\IndexInterpreter;
use PhpCfdi\RfcLinc\Updater\Updater;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class UpdaterTest extends DatabaseTestCase
{
    /** @var FactoryInterface */
    private $gateways;

    /** @var VersionDate */
    private $date;

    /** @var Updater */
    private $updater;

    protected function setUp()
    {
        parent::setUp();
        $this->date = new VersionDate(2018, 2, 11);
        $this->gateways = new PdoFactory($this->pdo());
        $this->updater = new Updater($this->date, $this->gateways);
    }

    public function testConstructed()
    {
        $this->assertSame($this->date, $this->updater->date());
        $this->assertSame($this->gateways, $this->updater->gateways());
        $this->assertInstanceOf(IndexInterpreter::class, $this->updater->indexInterpreter());
        $this->assertInstanceOf(DownloaderInterface::class, $this->updater->downloader());
        $this->assertInstanceOf(LoggerInterface::class, $this->updater->logger());
    }

    public function testLogger()
    {
        $logger = new NullLogger();
        $this->updater->setLogger($logger);
        $this->assertSame($logger, $this->updater->logger());
    }

    public function testInterpreter()
    {
        $indexInterpreter = new IndexInterpreter();
        $this->updater->setIndexInterpreter($indexInterpreter);
        $this->assertSame($indexInterpreter, $this->updater->indexInterpreter());
    }

    public function testProgress()
    {
        $progress = new NullProgress();
        $this->updater->setProgress($progress);
        $this->assertSame($progress, $this->updater->progress());
    }

    public function testDownloader()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|DownloaderInterface $downloader */
        $downloader = $this->createMock(DownloaderInterface::class);
        $this->updater->setDownloader($downloader);
        $this->assertSame($downloader, $this->updater->downloader());
    }

    public function testImporterWithoutRun()
    {
        $this->assertFalse($this->updater->hasImporter());

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('importer');

        $this->updater->importer();
    }

    public function testRunBlobs()
    {
        $this->cleanupDatabase();
        $source = $this->utilAsset('datasample-signed.txt.gz');
        $blob = new Blob('name', $source, 'Ziyi4Yp5ffzWu5hi/ivpUw==');
        $returned = $this->updater->runBlobs($blob);

        $version = $this->updater->version();
        $this->assertSame($this->date, $version->date());
        $this->assertSame(100, $returned, 'Expected 100 lines to be processed, looks like the reader fails');
        $this->assertSame(100, $version->inserted());
        $this->assertSame(0, $version->updated());
        $this->assertSame(0, $version->deleted());
        $this->assertSame(100, $version->records());
    }

    public function testRun()
    {
        $expectedIndexFile = $this->utilAsset('list-of-blobs.xml');

        /** @var \PHPUnit\Framework\MockObject\MockObject|Updater $updater */
        $updater = $this->getMockBuilder(Updater::class)
            ->setConstructorArgs([$this->date, $this->gateways])
            ->setMethodsExcept(['run', 'setDownloader'])
            ->getMock();
        $updater->expects($runBlobsSpy = $this->once())->method('runBlobs');

        // change downloader to ensure that it will only read/copy
        /** @var \PHPUnit\Framework\MockObject\MockObject|DownloaderInterface $downloader */
        $downloader = $this->getMockBuilder(DownloaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $downloader->method('download')->willReturn(file_get_contents($expectedIndexFile));
        $updater->setDownloader($downloader); // make sure to use a simple downloader

        // action!
        $updater->run();

        // asserts
        $invocations = $runBlobsSpy->getInvocations();
        $this->assertCount(1, $invocations);
        $invocation = $invocations[0];
        $this->assertCount(5, $invocation->getParameters());
    }

    public function testIndexUrl()
    {
        $url = Updater::buildIndexUrl($this->date);
        $this->assertStringStartsWith(Updater::URL_BLOBS_LIST, $url);
        $this->assertStringEndsWith($this->date->format('_'), $url);
        $this->assertSame($url, $this->updater->indexUrl());
    }

    public function testCheckFileMd5()
    {
        $file = $this->utilAsset('datasample-signed.txt.gz');
        $expectedMd5 = '662ca2e18a797dfcd6bb9862fe2be953';

        $this->updater->checkFileMd5($file, $expectedMd5);
        $this->assertTrue(true, 'checkFileMd5 did not create any exception');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($expectedMd5);
        $this->updater->checkFileMd5($file, $expectedMd5 . 'x');
    }
}
