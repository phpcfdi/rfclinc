<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application\Cli\Commands;

use PhpCfdi\RfcLinc\Application\Cli\Commands\CatalogCommand;
use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PhpCfdi\RfcLinc\Tests\Application\Cli\CliApplicationTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class CatalogCommandTest extends CliApplicationTestCase
{
    /**
     * @param string $input
     * @testWith ["latest"]
     *           ["2018-02-12"]
     */
    public function testWithMockedRun(string $input)
    {
        $version = new Catalog(new VersionDate(2018, 1, 13), 11, 12, 13, 14);

        // create the application
        $application = $this->createApplication();

        // create the mock command
        /** @var MockObject|CatalogCommand $command */
        $command = $this->getMockBuilder(CatalogCommand::class)
            ->setConstructorArgs([$application->container()])
            ->setMethods(['getLatestCatalog', 'getCatalogByDateString'])
            ->getMock();
        $command->method('getLatestCatalog')->willReturn($version);
        $command->method('getCatalogByDateString')->willReturn($version);

        // alter the application with the mock command
        $application->add($command);

        // run the command inside a tester
        $commandTester = new CommandTester($command);
        $commandTester->execute(['catalog' => $input], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);

        $display = $commandTester->getDisplay();
        $this->assertContains($input, $display);
        $this->assertContains('Catalog: ' . $version->date()->format(), $display);
        $this->assertContains('Active: ' . $version->records(), $display);
        $this->assertContains('Inserted: ' . $version->inserted(), $display);
        $this->assertContains('Updated: ' . $version->updated(), $display);
        $this->assertContains('Deleted: ' . $version->deleted(), $display);
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}
