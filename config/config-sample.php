<?php

declare(strict_types=1);

/**
 * The config array keys are in the config class
 * @see \RfcLinc\Application\Config
 */
return [
    'environment' => 'development',
    'db.dns' => 'sqlite:' . realpath(__DIR__ . '/../tests/assets/database.sqlite3'),
    'db.username' => '',
    'db.password' => '',
];
