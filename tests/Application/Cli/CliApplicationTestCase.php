<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application\Cli;

use PhpCfdi\RfcLinc\Application\Cli\Application;
use PhpCfdi\RfcLinc\Application\Config;
use PHPUnit\Framework\TestCase;

class CliApplicationTestCase extends TestCase
{
    public static function createApplication(): Application
    {
        $application = Application::createApplication();
        $application->container()['config'] = Config::createFromArray([
            Config::KEY_DB_DSN => 'sqlite::memory:',
        ]);
        $application->setAutoExit(false);
        return $application;
    }
}
