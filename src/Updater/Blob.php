<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Updater;

class Blob
{
    /** @var string */
    private $name;

    /** @var string */
    private $url;

    /** @var string */
    private $contentMd5;

    /** @var string */
    private $md5;

    public function __construct(
        string $name,
        string $url,
        string $contentMd5
    ) {
        $this->name = $name;
        $this->url = $url;
        $this->contentMd5 = $contentMd5;
        $this->md5 = $this->convertMd5BlobToMd5Standard($contentMd5);
    }

    public static function convertMd5BlobToMd5Standard(string $stringBase64): string
    {
        // base64 -> decoded string -> split to bytes -> map to hex -> implode all hex
        return implode(
            array_map(
                function (string $input): string {
                    return bin2hex($input);
                },
                str_split(
                    base64_decode($stringBase64) ? : '' // base64 can return FALSE
                ) ? : [] // str_split can return FALSE
            )
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function contentMd5(): string
    {
        return $this->contentMd5;
    }

    public function md5(): string
    {
        return $this->md5;
    }
}
