<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Util;

interface ReaderInterface
{
    /**
     * @param string $source
     * @return void
     */
    public function open(string $source);

    /**
     * @return string|false
     */
    public function readLine();

    /**
     * @return void
     */
    public function close();

    public function isOpen(): bool;
}
