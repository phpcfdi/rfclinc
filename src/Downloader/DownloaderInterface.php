<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Downloader;

interface DownloaderInterface
{
    public function download(string $url): string;

    public function downloadAs(string $url, string $filename);
}
