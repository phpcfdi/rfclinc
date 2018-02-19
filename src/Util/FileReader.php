<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Util;

class FileReader implements ReaderInterface
{
    /** @var resource|null */
    private $file;

    public function __destruct()
    {
        $this->close();
    }

    public function open(string $source)
    {
        $this->file = fopen($source, 'r');
        if (! is_resource($this->file)) {
            throw new \RuntimeException('Cannot create a reader from the file');
        }
    }

    public function readLine()
    {
        if (! is_resource($this->file)) {
            throw new \RuntimeException('File is not open');
        }
        if (feof($this->file)) {
            return false;
        }
        $line = fgets($this->file);
        return (false !== $line) ? rtrim($line, PHP_EOL) : false;
    }

    public function close()
    {
        if (is_resource($this->file)) {
            fclose($this->file);
        }
        $this->file = null;
    }

    public function isOpen(): bool
    {
        return is_resource($this->file);
    }
}
