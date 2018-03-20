<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Util;

class ShellWhich
{
    public function __invoke(string $executable): string
    {
        $output = [];
        $return = -1;
        exec('which ' . escapeshellarg($executable), $output, $return);
        if (0 !== (int) $return) {
            return '';
        }
        $count = count($output);
        if (0 === $count) {
            return '';
        }
        return $output[$count - 1];
    }
}
