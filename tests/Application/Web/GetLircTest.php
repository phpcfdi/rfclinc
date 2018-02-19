<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application\Web;

use PhpCfdi\RfcLinc\Domain\RfcLog;
use PhpCfdi\RfcLinc\Tests\Application\SilexTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetLircTest extends SilexTestCase
{
    public function testLircWithExpectedValue()
    {
        // CIAR800128CJ1 is known and has two logs
        $rfc = 'CIAR800128CJ1';
        $response = $this->doRequest($rfc);
        $this->assertTrue($response->isOk());

        $data = json_decode($response->getContent(), true) ? : [];
        $expectedData = [
            'rfc' => $rfc,
            'since' => '2018-02-11',
            'active' => true,
            'sncf' => false,
            'sub' => true,
            'logs' => [
                ['date' => '2018-02-11', 'action' => RfcLog::ACTION_CREATED],
                ['date' => '2018-02-12', 'action' => RfcLog::ACTION_CHANGE_SUB_ON],
            ],
        ];
        $this->assertEquals($expectedData, $data);
    }

    public function testLircWithNotFoundValue()
    {
        $rfc = 'XXXX010101XXA';
        $response = $this->doRequest($rfc);
        $this->assertTrue($response->isNotFound());

        $data = json_decode($response->getContent(), true) ? : [];
        $expectedData = [
            'error' => "The RFC $rfc does not exists",
        ];
        $this->assertEquals($expectedData, $data);
    }

    private function doRequest(string $rfc): Response
    {
        $client = $this->createClient();
        $url = 'lrfc/' . $rfc;
        $client->request('GET', $url);
        $response = $client->getResponse();
        if (null === $response) {
            return new Response("Test cannot request $url", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $response;
    }
}
