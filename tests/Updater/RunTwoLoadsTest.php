<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Updater;

use PDO;
use PhpCfdi\RfcLinc\DataGateway\Pdo\PdoFactory;
use PhpCfdi\RfcLinc\DataGateway\RfcLogGatewayInterface;
use PhpCfdi\RfcLinc\Domain\RfcLog;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PhpCfdi\RfcLinc\Tests\DatabaseTestCase;
use PhpCfdi\RfcLinc\Updater\Updater;
use PhpCfdi\RfcLinc\Util\FileReader;

/*
 * datasample-contents.txt vs datasample-contents-2.txt changes:
 *
 * CEGM940425861|0|0 => CEGM940425861|1|0
 * CERC7911221X1|0|0 => CERC7911221X1|1|1
 * CIAR800128CJ1|0|0 => CIAR800128CJ1|0|1
 * CIFM750811MFA|0|0 => (removed)
 * (inserted)        => CAXX800101PJ9|0|0
 */

/**
 * This test case overrides createPdo to create the database file test/assets/database.sqlite3
 * from test/assets/datasample-contents.txt & test/assets/datasample-contents-2.txt
 *
 * This is needed for SilexTestCase
 */
class RunTwoLoadsTest extends DatabaseTestCase
{
    protected function createPdo(): PDO
    {
        $dbfile = static::utilAsset('database.sqlite3');
        if (file_exists($dbfile)) {
            unlink($dbfile);
        }
        return new PDO('sqlite:' . $dbfile);
    }

    public function testTwoLoads()
    {
        // force to write the database file
        $pdo = $this->createPdo();
        $this->createDatabase($pdo);

        $gateways = new PdoFactory($pdo);
        $reader = new FileReader();

        $firstUpdater = new Updater(new VersionDate(2018, 2, 11), $gateways);
        $reader->open($this->utilAsset('datasample-contents.txt'));
        $firstUpdater->processBegin();
        $firstUpdater->processReader($reader);
        $firstUpdater->processEnd();
        $reader->close();

        $firstVersion = $firstUpdater->version();

        $this->assertSame([100, 100, 0, 0], [
            $firstVersion->records(),
            $firstVersion->inserted(),
            $firstVersion->updated(),
            $firstVersion->deleted(),
        ]);

        $secondUpdater = new Updater(new VersionDate(2018, 2, 12), $gateways);
        $reader->open($this->utilAsset('datasample-contents-2.txt'));
        $secondUpdater->processBegin();
        $secondUpdater->processReader($reader);
        $secondUpdater->processEnd();
        $reader->close();

        $secondVersion = $secondUpdater->version();
        $this->assertSame([100, 1, 3, 1], [
            $secondVersion->records(),
            $secondVersion->inserted(),
            $secondVersion->updated(),
            $secondVersion->deleted(),
        ]);

        $gwLogRfc = $gateways->rfclog();
        $expected = [
            ['date' => '2018-02-11', 'action' => RfcLog::ACTION_CREATED],
            ['date' => '2018-02-12', 'action' => RfcLog::ACTION_CHANGE_SNCF_ON],
        ];
        $this->assertEquals($expected, $this->retrieveLogsByRfc($gwLogRfc, 'CEGM940425861'));

        $gwLogRfc = $gateways->rfclog();
        $expected = [
            ['date' => '2018-02-11', 'action' => RfcLog::ACTION_CREATED],
            ['date' => '2018-02-12', 'action' => RfcLog::ACTION_CHANGE_SNCF_ON],
            ['date' => '2018-02-12', 'action' => RfcLog::ACTION_CHANGE_SUB_ON],
        ];
        $this->assertEquals($expected, $this->retrieveLogsByRfc($gwLogRfc, 'CERC7911221X1'));

        $gwLogRfc = $gateways->rfclog();
        $expected = [
            ['date' => '2018-02-11', 'action' => RfcLog::ACTION_CREATED],
            ['date' => '2018-02-12', 'action' => RfcLog::ACTION_CHANGE_SUB_ON],
        ];
        $this->assertEquals($expected, $this->retrieveLogsByRfc($gwLogRfc, 'CIAR800128CJ1'));

        $gwLogRfc = $gateways->rfclog();
        $expected = [
            ['date' => '2018-02-11', 'action' => RfcLog::ACTION_CREATED],
            ['date' => '2018-02-12', 'action' => RfcLog::ACTION_REMOVED],
        ];
        $this->assertEquals($expected, $this->retrieveLogsByRfc($gwLogRfc, 'CIFM750811MFA'));

        $gwLogRfc = $gateways->rfclog();
        $expected = [
            ['date' => '2018-02-12', 'action' => RfcLog::ACTION_CREATED],
        ];
        $this->assertEquals($expected, $this->retrieveLogsByRfc($gwLogRfc, 'CAXX800101PJ9'));
    }

    private function retrieveLogsByRfc(RfcLogGatewayInterface $gwRfcLog, string $rfc): array
    {
        $logs = [];
        foreach ($gwRfcLog->eachByRfc($rfc) as $rfcLog) {
            $logs[] = [
                'date' => $rfcLog->date()->format(),
                'action' => $rfcLog->action(),
            ];
        }
        return $logs;
    }
}
