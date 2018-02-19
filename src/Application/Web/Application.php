<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application\Web;

use PhpCfdi\RfcLinc\Application\SetUpContainerTrait;
use PhpCfdi\RfcLinc\Application\Web\Controllers\ListedRfcController;
use Silex\Application as SilexApp;

class Application extends SilexApp
{
    use SetUpContainerTrait;

    public static function createApplication(): self
    {
        // create
        $app = new static();
        static::setUpContainer($app);

        // register the only route
        $app->get('lrfc/{id}', function (SilexApp $app, string $id) {
            $controller = new ListedRfcController($app['gateways']);
            return $controller->get($id);
        });

        return $app;
    }
}
