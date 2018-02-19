<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application\Cli;

use PhpCfdi\RfcLinc\Application\Cli\Commands\CatalogCommand;
use PhpCfdi\RfcLinc\Application\Cli\Commands\UpdateCommand;
use PhpCfdi\RfcLinc\Application\SetUpContainerTrait;
use Pimple\Container;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{
    use SetUpContainerTrait;

    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct('rfclinc', '0.1.0');
        $this->container = $container;
    }

    public function container(): Container
    {
        return $this->container;
    }

    public static function createApplication(): self
    {
        // create
        $containter = new Container();
        $app = new static($containter);

        static::setUpContainer($containter);

        // pass container to avoid gateway creation
        $app->add(new UpdateCommand($containter));
        $app->add(new CatalogCommand($containter));

        return $app;
    }
}
