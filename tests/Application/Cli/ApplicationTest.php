<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application\Cli;

use Symfony\Component\Console\Tester\ApplicationTester;

class ApplicationTest extends CliApplicationTestCase
{
    public function testApplicationWithCommandNotFound()
    {
        $application = $this->createApplication();

        $applicationTester = new ApplicationTester($application);
        $applicationTester->run(['command-not-found']);
        $this->assertSame(1, $applicationTester->getStatusCode());
    }

    public function testApplicationWithUpdateWithoutDate()
    {
        $application = $this->createApplication();

        $applicationTester = new ApplicationTester($application);
        $applicationTester->run(['update']);
        $this->assertContains('date', $applicationTester->getDisplay());
        $this->assertSame(1, $applicationTester->getStatusCode());
    }
}
