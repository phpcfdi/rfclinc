<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application;

class Config
{
    const KEY_ENVIRONMENT = 'environment';

    const VALUE_PRODUCTION = 'production';

    const KEY_DB_DSN = 'db.dsn';

    const KEY_DB_USERNAME = 'db.username';

    const KEY_DB_PASSWORD = 'db.password';

    /** @var bool */
    private $environmentProduction;

    /** @var string */
    private $dbDsn;

    /** @var string */
    private $dbUsername;

    /** @var string */
    private $dbPassword;

    public function __construct(bool $environmentProduction, string $dbDsn, string $dbUsername, string $dbPassword)
    {
        $this->environmentProduction = $environmentProduction;
        $this->dbDsn = $dbDsn;
        $this->dbUsername = $dbUsername;
        $this->dbPassword = $dbPassword;
    }

    public static function createFromConfigFile(string $filename): self
    {
        try {
            if (! file_exists($filename)) {
                throw new \RuntimeException('It does not exists');
            }
            if (is_dir($filename)) {
                throw new \RuntimeException('It is a directory');
            }
            if (! is_readable($filename)) {
                throw new \RuntimeException('It is not readable');
            }
            /** @noinspection PhpIncludeInspection */
            $settings = require $filename;
            if (! is_array($settings)) {
                throw new \RuntimeException('The file did not return an array');
            }
            return self::createFromArray($settings);
        } catch (\Throwable $exception) {
            throw new \RuntimeException("Cannot read config file $filename", 0, $exception);
        }
    }

    public static function createFromArray(array $values): self
    {
        return new self(
            'production' === ($values[static::KEY_ENVIRONMENT] ?? ''),
            (string) ($values[static::KEY_DB_DSN] ?? ''),
            (string) ($values[static::KEY_DB_USERNAME] ?? ''),
            (string) ($values[static::KEY_DB_PASSWORD] ?? '')
        );
    }

    public function isEnvironmentProduction(): bool
    {
        return $this->environmentProduction;
    }

    public function dbDsn(): string
    {
        return $this->dbDsn;
    }

    public function dbUsername(): string
    {
        return $this->dbUsername;
    }

    public function dbPassword(): string
    {
        return $this->dbPassword;
    }
}
