<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function utilAsset(string $filename): string
    {
        return __DIR__ . '/assets/' . $filename;
    }
}
