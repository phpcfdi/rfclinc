<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application;

use PhpCfdi\RfcLinc\Application\Config;
use PhpCfdi\RfcLinc\Application\Web\Application;
use PhpCfdi\RfcLinc\Tests\TestCase;
use Silex\WebTestCase;

class SilexTestCase extends WebTestCase
{
    public function createApplication(): Application
    {
        $database = TestCase::utilAsset('database.sqlite3');
        if (! file_exists($database)) {
            $this->markTestSkipped('The database does not exists, try again');
        }
        $application = Application::createApplication();
        $application['config'] = Config::createFromArray([
            Config::KEY_DB_DSN => 'sqlite://' . $database,
        ]);
        unset($application['exception_handler']);
        // set session.test to true to simulate sessions
        // $app['session.test'] = true;
        return $application;
    }
}
