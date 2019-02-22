#!/bin/env php
<?php

declare(strict_types=1);

use PhpCfdi\RfcLinc\Application\Cli\Application;

call_user_func(function () {

    // autoloader
    $autoloaders = [
        __DIR__ . '/../vendor/autoload.php', // running from source code /bin/
        __DIR__ . '/../../../autoload.php', // running from vendor/<vendor>/<package>/bin/
        __DIR__ . '/../autoload.php', // running from vendor/bin/
    ];
    foreach ($autoloaders as $autoloaderFile) {
        if (file_exists($autoloaderFile) && is_readable($autoloaderFile)) {
            /** @noinspection PhpIncludeInspection */
            require $autoloaderFile;
            break;
        }
    }

    // run application
    Application::createApplication()->run();
});
