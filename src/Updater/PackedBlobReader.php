<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Updater;

use PhpCfdi\RfcLinc\Util\CommandReader;
use PhpCfdi\RfcLinc\Util\ReaderInterface;
use PhpCfdi\RfcLinc\Util\ShellWhich;

class PackedBlobReader implements ReaderInterface
{
    /** @var CommandReader */
    private $reader;

    /** @var string[] */
    private $commands;

    public function __construct()
    {
        $this->reader = new CommandReader();
        $this->commands = $this->commandPaths();
    }

    public function commandPaths(): array
    {
        $which = new ShellWhich();
        $commands = [
            'gunzip' => $which('gunzip'),
            'openssl' => $which('openssl'),
            'iconv' => $which('iconv'),
            'sed' => $which('sed'),
        ];
        foreach ($commands as $command => $path) {
            if ('' === $path) {
                throw new \InvalidArgumentException("Cannot find $command, it is required to update");
            }
        }
        return $commands;
    }

    private function createCommandString($filename): string
    {
        $command = implode(' | ', [
            $this->commands['gunzip'] . ' --stdout ' . escapeshellarg($filename),
            $this->commands['openssl'] . ' smime -verify -in - -inform der -noverify 2> /dev/null',
            $this->commands['iconv'] . ' --from iso8859-1 --to utf-8',
            $this->commands['sed'] . ' ' . escapeshellarg('s/\r$//'),
        ]);
        return $command;
    }

    public function open(string $source)
    {
        $command = $this->createCommandString($source);
        $this->reader = new CommandReader();
        $this->reader->open($command);
    }

    public function readLine()
    {
        return $this->reader->readLine();
    }

    public function close()
    {
        $this->reader->close();
    }

    public function isOpen(): bool
    {
        return $this->reader->isOpen();
    }
}
