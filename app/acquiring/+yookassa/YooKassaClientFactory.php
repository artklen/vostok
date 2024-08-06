<?php

use YooKassa\Client;

class YooKassaClientFactory
{
    public static function create(): Client
    {
        $client = new Client();
        $client->setAuth((int) $_ENV['YOOKASSA_LOGIN'], $_ENV['YOOKASSA_PASSWORD']);
        $client->setLogger(YooKassaLog::get());
        return $client;
    }
}