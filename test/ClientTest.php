<?php

namespace YandexTaxiCorpClient;

use YandexTaxiCorpClient\Client;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClientTest extends TestCase
{

    private function createClient()
    {
        $fileName = __DIR__ . '/../oauthkey.txt';
        $file = fopen($fileName, 'r') or die('Cant open the oAuth key');
        $key = trim(fread($file, filesize($fileName)));
        fclose($file);
        return new Client($key, ['prefix' => 'https://corp-client.taxi.tst.yandex.ru/']);
    }
    public function testOAuthKey(): void
    {
        $client = new Client('testKey');
        static::assertEquals($client->getOAuthKey(), 'testKey');
    }

    public function testDefaultPrefix(): void
    {
        $client = new Client('testKey');
        static::assertEquals($client->getPrefix(), 'https://business.taxi.yandex.ru');
    }

    public function testConvertPositionToAddress()
    {
        $client = $this->createClient();
    }

    public function getTestSource()
    {
        return [
            "country" => "Россия",
            "full_text" => "Россия, Москва, улица Воздвиженка, 4/7с1",
            "short_text" => "улица Воздвиженка, 4/7с1",
            "short_text_from" => "улица Воздвиженка, 4/7с1",
            "short_text_to" => "улица Воздвиженка, 4/7с1",
            "point" => [37.60936701562499, 55.75253245573649],
            "city" => "Москва",
            "house" => "4/7с1",
            "thoroughfare" => "улица Воздвиженка",
            "type" => "address",
            "object_type" => "другое"
        ];
    }

    public function testConvertTextAddress()
    {
        $client = $this->createClient();
    }

    public function testGetAddress()
    {
        $client = $this->createClient();
        $response = $client->suggestAddresses('Кремль', 60.473366, 56.785866);
    }
    public function testGetAddressByCoordinates()
    {
        $client = $this->createClient();
        $response = $client->getAddressByPosition(60.473366, 56.785866);
    }

    public function testGetNearestPickupPoint()
    {
        $client = $this->createClient();
        $response = $client->getNearestPickupPoint(60.473366, 56.785866);
    }



    public function testEstimate()
    {
        $client = $this->createClient();
        $client->authenticate();
        $source = $client->getAddressByPosition(60.473366, 56.785866);
        $destination = $client->getAddressByPosition(60.473366, 56.895866);
        $estimate = $client->estimate(new EstimateParams(
            '79045423839',
            [new RoutePoint($source), new RoutePoint($destination)],
            'econom',
            array()
        ));
    }

    public function testEstimatedOrder()
    {
        $client = $this->createClient();
        $client->authenticate();
        $source = $client->getAddressByPosition(37.60936701562499, 55.75253245573649);
        $destination = $client->getAddressByPosition(37.60936701562499, 55.76253245573649);
        $response = $client->estimate(new EstimateParams(
            '79045423839',
            [new RoutePoint($source), new RoutePoint($destination)],
            'comfortplus',
            array(new BooleanRequirement('animaltransport', true))
        ));
        $order = Order::createFromEstimate($response);
        $id = $client->createOrder($order);
        $client->commitOrder($id);
    }

    public function testInitialize()
    {
        $client = $this->createClient();
        $client->authenticate();
    }
}
