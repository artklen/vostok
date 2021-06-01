<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * @see https://tariff.pochta.ru/post-calculator-api.pdf
 */
class RussianPost
{
    private const COST_URI = 'calculate/tariff/delivery';
    private const NON_STANDARD_PACKAGE_WITH_DECLARED_VALUE = 4020;
    private const PACK_BOX_S = 10;

    private $client;

    public function __construct()
    {
        $this->client = new Client(
            [
                'base_uri' => $_ENV['POST_BASE_URI'],
                'timeout' => 120.0,
            ]
        );
    }

    public function cost(string $toPostIndex, float $declaredValue): float
    {
        if ($toPostIndex === '') {
            return 0.;
        }

        $fromPostIndex = d()->Option->delivery_from_post_index;
        $packageWeight = d()->Option->delivery_package_weight;

        $response = $this->getQuery(
            self::COST_URI,
            [
                'object' => self::NON_STANDARD_PACKAGE_WITH_DECLARED_VALUE,
                'weight' => $packageWeight,
                'from' => $fromPostIndex,
                'to' => $toPostIndex,
                'pack' => self::PACK_BOX_S,
                'jsontext' => '',
                'sumoc' => ceil(100 * $declaredValue),
            ]
        );

        /* Итоговая сумма платы без НДС в копейках (в валюте расчета) */
        $pay = $response['pay'] ?? 0;

        return $pay / 100.;
    }

    private function getQuery(string $uri, array $query): array
    {
        try {
            $response = $this->client->get($uri, ['query' => $query]);
            return $this->decode($response) ?? [];
        } catch (GuzzleException $e) {
        }

        return [];
    }

    private function decode($response): array
    {
        return json_decode($response->getBody()->getContents(), true);
    }

}