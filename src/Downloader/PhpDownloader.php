<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Downloader;

class PhpDownloader implements DownloaderInterface
{
    public function download(string $url): string
    {
        return file_get_contents($url, false, stream_context_create([
            'http' => [
                'protocol_version' => '1.1',
            ],
        ]));
    }

    public function downloadAs(string $url, string $filename)
    {
        copy($url, $filename);
    }
}
