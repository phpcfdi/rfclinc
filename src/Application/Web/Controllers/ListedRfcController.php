<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application\Web\Controllers;

use PhpCfdi\RfcLinc\DataGateway\FactoryInterface;
use PhpCfdi\RfcLinc\DataGateway\NotFoundException;
use PhpCfdi\RfcLinc\Domain\RfcLog;
use Symfony\Component\HttpFoundation\JsonResponse;

class ListedRfcController
{
    /** @var FactoryInterface */
    private $gateways;

    public function __construct(FactoryInterface $gateways)
    {
        $this->gateways = $gateways;
    }

    public function gateways(): FactoryInterface
    {
        return $this->gateways;
    }

    public function get(string $id): JsonResponse
    {
        try {
            $gateways = $this->gateways();
            $listedRfc = $gateways->listedRfc()->get($id);
            $rfclogs = $gateways->rfclog()->byRfc($id);

            return new JsonResponse([
                'rfc' => $listedRfc->rfc(),
                'since' => $listedRfc->since()->format(),
                'sncf' => $listedRfc->sncf(),
                'sub' => $listedRfc->sub(),
                'active' => ! $listedRfc->deleted(),
                'logs' => array_map(function (RfcLog $rfcLog) {
                    return [
                        'date' => $rfcLog->date()->format(),
                        'action' => $rfcLog->action(),
                    ];
                }, $rfclogs),
            ], JsonResponse::HTTP_OK);
        } catch (NotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
