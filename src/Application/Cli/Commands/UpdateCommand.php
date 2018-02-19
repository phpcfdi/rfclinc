<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application\Cli\Commands;

use PhpCfdi\RfcLinc\Application\Cli\OutputLogger;
use PhpCfdi\RfcLinc\Application\Cli\ProgressByHit;
use PhpCfdi\RfcLinc\Application\Config;
use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use PhpCfdi\RfcLinc\Updater\Blob;
use PhpCfdi\RfcLinc\Updater\Updater;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct('update');
        $this->container = $container;
    }

    public function gateways(): FactoryInterface
    {
        return $this->container['gateways'];
    }

    public function config(): Config
    {
        return $this->container['config'];
    }

    protected function configure()
    {
        $this->setDescription('Update the database with a new catalog');
        $this->addArgument('date', InputArgument::REQUIRED, 'Update the database to this date');
        $this->addArgument(
            'blobs',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Use this blobs instead of downloading'
        );
        $this->addOption(
            'report-every',
            're',
            InputOption::VALUE_OPTIONAL,
            'Update the database to this date',
            '500000'
        );
    }

    public function getOptionReportEvery(string $value): int
    {
        $inputReportEvery = $value;
        if (! is_numeric($inputReportEvery)) {
            $inputReportEvery = 5000000;
        } else {
            $inputReportEvery = (int) $inputReportEvery;
        }
        return $inputReportEvery;
    }

    public function getArgumentDate(string $value): VersionDate
    {
        return VersionDate::createFromString($value);
    }

    /**
     * @return VersionDate|null
     */
    public function getLatestVersionDate()
    {
        $latest = $this->gateways()->catalog()->latest();
        if ($latest instanceof Catalog) {
            return $latest->date();
        }
        return null;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // input arguments
        $reportEvery = $this->getOptionReportEvery((string) $input->getOption('report-every'));
        $date = $this->getArgumentDate((string) $input->getArgument('date'));

        // create logger
        $logger = new OutputLogger($output);

        // report working info
        $logger->notice('Update date: ' . $date->format());
        $logger->debug(sprintf('Report progress every %d lines', $reportEvery));

        // show debug messages of database config
        $config = $this->config();
        $logger->debug(sprintf('DB: [%s], Username: [%s]', $config->dbDns(), $config->dbUsername()));

        // verify previous version
        $latestDate = $this->getLatestVersionDate();
        if (null !== $latestDate) {
            $logger->debug(sprintf('Latest catalog is %s', $latestDate->format()));
            if ($date->compare($latestDate) <= 0) {
                throw new \RuntimeException(sprintf(
                    'The update date %s is less or equal to the latest catalog %s',
                    $date->format(),
                    $latestDate->format()
                ));
            }
        } else {
            $logger->debug('Cannot found any previus catalog');
        }

        // check upper boud
        $today = VersionDate::createFromString('today');
        if ($date->compare($today) > 0) {
            throw new \RuntimeException(sprintf(
                'The update date %s is greater than today %s',
                $date->format(),
                $today->format()
            ));
        }

        $updater = $this->createUpdater($date);
        if (! $output->isQuiet()) {
            $updater->setLogger($logger);

            $progress = new ProgressByHit(null, [], $reportEvery);
            $progress->attach($logger);
            $updater->setProgress($progress);
        }

        // separate into a different method to allow mock & test
        $blobFiles = $input->getArgument('blobs');
        if (count($blobFiles)) {
            $blobs = [];
            foreach ($blobFiles as $index => $blobSourceFile) {
                $blobFile = (string) realpath($blobSourceFile);
                if ('' === $blobFile) {
                    throw new \RuntimeException('Cannot find file ' . $blobSourceFile);
                }
                $blobs[] = new Blob($blobFile, 'file://' . $blobFile, '');
            }
            $this->runUpdaterWithBlobs($updater, ...$blobs);
        } else {
            $this->runUpdater($updater);
        }

        return 0;
    }

    public function runUpdater(Updater $updater)
    {
        $updater->run();
    }

    public function runUpdaterWithBlobs(Updater $updater, Blob ...$blobs)
    {
        $updater->runBlobs(...$blobs);
    }

    public function createUpdater(VersionDate $date): Updater
    {
        return new Updater($date, $this->gateways());
    }
}
