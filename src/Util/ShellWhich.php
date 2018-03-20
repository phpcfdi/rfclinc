<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Util;

class ShellWhich
{
    public function __invoke(string $executable): string
    {
        $lines = [];
        $return = -1;
        $output = (string) exec('which ' . escapeshellarg($executable), $lines, $return);
        if (0 !== (int) $return) {
            return '';
        }
        return $output;
    }
}
