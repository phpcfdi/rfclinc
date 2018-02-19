<?php

declare(strict_types=1);

use PhpCfdi\RfcLinc\Application\Web\Application;

require_once __DIR__ . '/../vendor/autoload.php';

Application::createApplication()->run();
