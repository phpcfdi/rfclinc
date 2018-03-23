<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Updater;

use EngineWorks\ProgressStatus\Progress;
use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\DataGateway\Pdo\PdoFactory;
use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\RfcLog;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PhpCfdi\RfcLinc\Tests\DatabaseTestCase;
use PhpCfdi\RfcLinc\Tests\ProgressObserver;
use PhpCfdi\RfcLinc\Updater\Importer;
use PhpCfdi\RfcLinc\Util\FileReader;

class ImporterTest extends DatabaseTestCase
{
    /** @var FactoryInterface */
    private $gateways;

    /** @var Catalog */
    private $catalog;

    /** @var ProgressObserver */
    private $observer;

    /** @var Importer */
    private $importer;

    protected function setUp()
    {
        parent::setUp();
        $this->catalog = new Catalog(new VersionDate(2018, 2, 11), 0, 0, 0, 0);
        $this->gateways = new PdoFactory($this->pdo());
        $this->observer = new ProgressObserver();
        $this->importer = new Importer($this->catalog, $this->gateways, new Progress(null, [$this->observer]));
    }

    public function testConstructed()
    {
        $this->assertSame($this->catalog, $this->importer->catalog());
    }

    public function testIncrements()
    {
        $expected = [
            $this->catalog->inserted() + 1,
            $this->catalog->updated() + 2,
            $this->catalog->deleted() + 3,
        ];

        $this->importer->incrementInserted();
        $this->importer->incrementUpdated();
        $this->importer->incrementUpdated();
        $this->importer->incrementDeleted();
        $this->importer->incrementDeleted();
        $this->importer->incrementDeleted();

        $actual = [
            $this->catalog->inserted(),
            $this->catalog->updated(),
            $this->catalog->deleted(),
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testImportLine()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|Importer $importer */
        $importer = $this->getMockBuilder(Importer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['importLine'])
            ->getMock();

        $this->assertFalse($importer->importLine(''));
        $this->assertFalse($importer->importLine('EOF'));
        $this->assertFalse($importer->importLine('RFC|SNCF|SUBCONTRATACION'));
        $this->assertFalse($importer->importLine('AAA000103U53|0|X'));
        $this->assertTrue($importer->importLine('AAA000103U53|0|0'));
        $this->assertTrue($importer->importLine('AAA000103U53|0|1'));
    }

    public function testImportRecords()
    {
        $rfc = 'COSC8001137NA';
        $gwListedRfc = $this->gateways->listedRfc();

        // cleanup database
        $this->cleanupDatabase();

        // this do an insert
        $this->importer->importLine("$rfc|0|0");
        $this->assertTrue($gwListedRfc->exists($rfc));
        $inserted = $gwListedRfc->get($rfc);
        $this->assertSame($rfc, $inserted->rfc());
        $this->assertFalse($inserted->sncf());
        $this->assertFalse($inserted->sub());
        $this->assertArraySubset([
            0 => [
                'version' => $this->catalog->date()->format(),
                'action' => RfcLog::ACTION_CREATED,
            ],
        ], $this->retrieveLogs($rfc));

        // this will do an update changing sncf
        $this->importer->importLine("$rfc|1|0");
        $updatedOne = $gwListedRfc->get($rfc);
        $this->assertTrue($updatedOne->sncf());
        $this->assertFalse($updatedOne->sub());
        $this->assertArraySubset([
            1 => ['action' => RfcLog::ACTION_CHANGE_SNCF_ON],
        ], $this->retrieveLogs($rfc));

        // this will do an update changing sub
        $this->importer->importLine("$rfc|0|1");
        $updatedTwo = $gwListedRfc->get($rfc);
        $this->assertFalse($updatedTwo->sncf());
        $this->assertTrue($updatedTwo->sub());
        $this->assertArraySubset([
            2 => ['action' => RfcLog::ACTION_CHANGE_SNCF_OFF],
            3 => ['action' => RfcLog::ACTION_CHANGE_SUB_ON],
        ], $this->retrieveLogs($rfc));

        // this will do an update changing sub to 0
        $this->importer->importLine("$rfc|0|0");
        $updatedTree = $gwListedRfc->get($rfc);
        $this->assertFalse($updatedTree->sncf());
        $this->assertFalse($updatedTree->sub());
        $this->assertArraySubset([
            4 => ['action' => RfcLog::ACTION_CHANGE_SUB_OFF],
        ], $this->retrieveLogs($rfc));

        // check numbers
        $this->assertSame(1, $this->catalog->inserted());
        $this->assertSame(3, $this->catalog->updated());
        $this->assertSame(0, $this->catalog->deleted());
    }

    public function testPerformDelete()
    {
        $rfc = 'COSC800113';

        // cleanup database
        $this->cleanupDatabase();

        // perform the delete
        $this->importer->performDelete($rfc);
        $this->assertArraySubset([
            0 => ['action' => RfcLog::ACTION_REMOVED],
        ], $this->retrieveLogs($rfc));
        $this->assertSame(1, $this->catalog->deleted());
    }

    public function testImportReaderWithObserver()
    {
        $source = $this->utilAsset('datasample-contents.txt');
        $this->assertFileExists($source);

        // cleanup database
        $this->cleanupDatabase();

        // do the import
        $reader = new FileReader();
        $reader->open($source);
        $this->importer->importReader($reader);
        $reader->close();

        $this->assertSame(100, $this->catalog->inserted());
        $this->assertSame(0, $this->catalog->updated());
        $this->assertSame(0, $this->catalog->deleted());

        $this->assertSame(100, $this->observer->lastStatus->getValue());
    }

    /**
     * @param string $rfc
     * @return RfcLog[]
     */
    private function retrieveLogs(string $rfc): array
    {
        $rfclogs = [];
        foreach ($this->gateways->rfclog()->eachByRfc($rfc) as $rfclog) {
            $rfclogs[] = [
                'version' => $rfclog->date()->format(),
                'rfc' => $rfclog->rfc(),
                'action' => $rfclog->action(),
            ];
        }
        return $rfclogs;
    }
}
