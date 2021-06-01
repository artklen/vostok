<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Cdek
{
    private $client;

    private $access_token;

    public function __construct()
    {
        $this->client = new Client(
            [
                'base_uri' => $_ENV['CDEK_BASE_URI'],
                'timeout' => 120.0,
            ]
        );
    }

    /**
     * @return array<CdekCity>
     * @see https://confluence.cdek.ru/pages/viewpage.action?pageId=33829437
     */
    public function cities(): array
    {
        $this->authorize();

        $response = $this->getQuery('location/cities?size=1000000');

        foreach ($response as $row) {
            $result[] = (d()->CdekCity
                ->code($row['code'])
                ->title($row['city'])
                ->region($row['region'])
                ->subregion($row['sub_region'])
                ->fias($row['fias_guid'] ?? '')
            );
        }

        return $result ?? [];
    }

    /**
     * @see https://confluence.cdek.ru/pages/viewpage.action?pageId=29923918
     */
    private function authorize(): void
    {
        if (isset($this->access_token)) {
            return;
        }

        try {
            $response = $this->client->post(
                'oauth/token?parameters',
                [
                    'form_params' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => $_ENV['CDEK_LOGIN'],
                        'client_secret' => $_ENV['CDEK_PASSWORD'],
                    ],
                ]
            );
            $this->access_token = $this->decode($response)['access_token'];
        } catch (GuzzleException $e) {
        }
    }

    private function decode($response): array
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    private function getQuery(string $url): array
    {
        try {
            $response = $this->client->get($url, $this->requestOptions());
            return $this->decode($response) ?? [];
        } catch (GuzzleException $e) {
            //print $_ENV['CDEK_BASE_URI'] . $url;
            //var_dump($e->getMessage());
            //exit;
        }
        return [];
    }

    private function requestOptions(): array
    {
        return [
            GuzzleHttp\RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $this->access_token,
            ],
        ];
    }

    /**
     * @return array<CdekPoint>
     * @see https://confluence.cdek.ru/pages/viewpage.action?pageId=36982648
     */
    public function points($cityCode): array
    {
        $this->authorize();

        $response = $this->getQuery('deliverypoints?city_code=' . ((int) $cityCode));

        foreach ($response as $row) {
            $result[] = d()->CdekPoint
                ->code($row['code'])
                ->name($row['name'])
                ->address($row['location']['address'])
                ->comment($row['address_comment'])
                ->workingHours($row['work_time'])
                ->coords($row['location']['latitude'] . ',' . $row['location']['longitude']);
        }

        return $result ?? [];
    }

    public function pointCost(int $to_location_code): float
    {
        return $this->cost($to_location_code, (int) $_ENV['CDEK_POINT_TARIFF_CODE']);
    }

    /**
     * @see https://confluence.cdek.ru/pages/viewpage.action?pageId=63345430
     */
    private function cost(int $to_location_code, int $tariff_code): float
    {
        $this->authorize();

        $from_location_code = (int) $_ENV['CDEK_FROM_LOCATION_CODE'];
        $package_weight = (int) d()->Option->delivery_package_weight;
        $package_length = (int) d()->Option->delivery_package_length;
        $package_width = (int) d()->Option->delivery_package_width;
        $package_height = (int) d()->Option->delivery_package_height;

        $request = [
            'tariff_code' => $tariff_code,
            'from_location' => [
                'code' => $from_location_code,
            ],
            'to_location' => [
                'code' => $to_location_code,
            ],
            'packages' => [
                [
                    'weight' => $package_weight,
                    'length' => $package_length,
                    'width' => $package_width,
                    'height' => $package_height,
                ]
            ],
        ];

        $response = $this->postQuery('calculator/tariff', $request);
        return $response['total_sum'] ?? 0.;
    }

    private function postQuery(string $url, $request): array
    {
        try {
            $options = $this->requestOptions();
            $options['json'] = $request;

            $response = $this->client->post($url, $options);

            return $this->decode($response) ?? [];
        } catch (GuzzleException $e) {
            //print $_ENV['CDEK_BASE_URI'] . $url;
            //var_dump($options);
            //var_dump($e->getMessage());
            //if ($e instanceof ClientException) {
            //    var_dump($e->getResponse()->getBody()->getContents());
            //}
            //exit;
        }
        return [];
    }

    public function courierCost(int $to_location_code): float
    {
        return $this->cost($to_location_code, (int) $_ENV['CDEK_COURIER_TARIFF_CODE']);
    }
}