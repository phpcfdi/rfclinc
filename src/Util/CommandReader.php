<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Util;

class CommandReader implements ReaderInterface
{
    /** @var resource|null */
    private $process;

    /** @var resource|null */
    private $inputPipe;

    public function __destruct()
    {
        $this->close();
    }

    public function open(string $command)
    {
        $this->close();
        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        // if the process is not a resource
        if (! is_resource($process)) {
            throw new \RuntimeException('Cannot create a reader from the command');
        }

        // close stdin and stderr
        foreach ([0, 2] as $pipeIndex) {
            if (isset($pipes[$pipeIndex]) && is_resource($pipes[$pipeIndex])) {
                fclose($pipes[$pipeIndex]);
            }
        }

        // setup object
        $this->process = $process;
        $this->inputPipe = $pipes[1];
    }

    public function readLine()
    {
        if (! is_resource($this->process)) {
            throw new \RuntimeException('File is not open (command)');
        }
        if (! is_resource($this->inputPipe)) {
            throw new \RuntimeException('File is not open (pipe)');
        }
        return stream_get_line($this->inputPipe, 1024, PHP_EOL);
    }

    public function close()
    {
        if (is_resource($this->inputPipe)) {
            fclose($this->inputPipe);
            $this->inputPipe = null;
        }
        if (is_resource($this->process)) {
            $status = proc_get_status($this->process) ? : [];
            if ($status['running'] ?? false) {
                proc_terminate($this->process);
            }
            proc_close($this->process);
            $this->process = null;
        }
    }

    public function isOpen(): bool
    {
        return is_resource($this->process);
    }
}
