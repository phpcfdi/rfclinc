<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Updater;

use SimpleXMLElement;

class IndexInterpreter
{
    /**
     * @param string $source
     * @return Blob[]
     */
    public function obtainBlobs(string $source): array
    {
        try {
            $xml = new SimpleXMLElement($source, LIBXML_NONET);
        } catch (\Exception $exception) {
            throw new \DomainException('The source is not a valid xml content', 0, $exception);
        }
        if (! isset($xml->{'Blobs'}) || ! isset($xml->{'Blobs'}->{'Blob'})) {
            return [];
        }
        $blobs = [];
        foreach ($xml->{'Blobs'}->{'Blob'} as $xmlBlob) {
            $blobs[] = $this->blobFromSimpleXml($xmlBlob);
        }
        return $blobs;
    }

    public function blobFromSimpleXml(SimpleXMLElement $xmlBlob): Blob
    {
        return new Blob(
            (string) $xmlBlob->{'Name'},
            (string) $xmlBlob->{'Url'},
            (string) $xmlBlob->{'Properties'}->{'Content-MD5'}
        );
    }
}
