<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application\Cli\Commands;

use PhpCfdi\RfcLinc\Application\Cli\Commands\UpdateCommand;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PhpCfdi\RfcLinc\Tests\Application\Cli\CliApplicationTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateCommandTest extends CliApplicationTestCase
{
    public function testWithMockedRun()
    {
        // create the application
        $application = $this->createApplication();

        // create the mock command
        /** @var MockObject|UpdateCommand $command */
        $command = $this->getMockBuilder(UpdateCommand::class)
            ->setConstructorArgs([$application->container()])
            ->setMethods(['runUpdater', 'getLatestVersionDate'])
            ->getMock();
        $command->method('getLatestVersionDate')->willReturn(VersionDate::createFromString('yesterday'));
        $command->expects($this->exactly(1))->method('runUpdater');

        // alter the application with the mock command
        $application->add($command);

        // run the command inside a tester
        $strDate = date('Y-m-d', strtotime('today'));
        $commandTester = new CommandTester($command);
        $commandTester->execute(['date' => $strDate], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);
        $display = $commandTester->getDisplay();
        $this->assertContains("Update date: $strDate", $display);
        $this->assertNotContains('Processed', $display);
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}
