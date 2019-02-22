<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application\Cli\Commands;

use PhpCfdi\RfcLinc\Application\Cli\OutputLogger;
use PhpCfdi\RfcLinc\Application\Config;
use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\Domain\Catalog;
use PhpCfdi\RfcLinc\Domain\VersionDate;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CatalogCommand extends Command
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct('catalog');
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
        $this->setDescription('Obtain catalog information');
        $this->addArgument('catalog', InputArgument::REQUIRED, 'Catalog (as date) to request, can use "latest"');
    }

    public function getLatestCatalog(): Catalog
    {
        $latest = $this->gateways()->catalog()->latest();
        if ($latest instanceof Catalog) {
            return $latest;
        }
        throw new \RuntimeException('There are no latest catalog in the database');
    }

    protected function getCatalogByDateString(string $dateString): Catalog
    {
        return $this->gateways()->catalog()->get(
            VersionDate::createFromString($dateString)
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // input arguments
        $argumentCatalog = (string) $input->getArgument('catalog');

        // create logger
        $logger = new OutputLogger($output);

        // report working info
        $logger->info('Required catalog: ' . $argumentCatalog);

        // show debug messages of database config
        $config = $this->config();
        $logger->debug(sprintf('DB: [%s], Username: [%s]', $config->dbDsn(), $config->dbUsername()));

        // get data
        if ('latest' === $argumentCatalog) {
            $catalog = $this->getLatestCatalog();
        } else {
            $catalog = $this->getCatalogByDateString($argumentCatalog);
        }

        // print data
        $logger->notice(sprintf(
            'Catalog: %s, Active: %s, Inserted: %s, Updated: %s, Deleted: %s',
            $catalog->date()->format(),
            number_format($catalog->records(), 0),
            number_format($catalog->inserted(), 0),
            number_format($catalog->updated(), 0),
            number_format($catalog->deleted(), 0)
        ));

        return 0;
    }
}
