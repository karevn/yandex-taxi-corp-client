<?php

namespace YandexTaxiCorpClient;

use GuzzleHttp\Client as HttpClient;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
use stdClass;

class Client
{
    public const DEFAULT_PREFIX = 'https://business.taxi.yandex.ru';
    private string $oAuthKey;
    private string $prefix;
    private HttpClient $client;
    private array $authResult;

    /**
     * Надо передать полученный в КК ключ авторизации и необязательные опции.
     */
    public function __construct(string $oAuthKey, array $options = [])
    {
        $this->prefix = array_key_exists('prefix', $options) ? $options['prefix'] : self::DEFAULT_PREFIX;
        $this->oAuthKey = $oAuthKey;

        $this->client = new HttpClient([
            'base_uri' => $this->prefix,
            'verify' => false
        ]);
    }

    public function authenticate()
    {
        $response = $this->client->get('api/auth', ['headers' => $this->getHeaders()]);
        $this->authResult = json_decode((string) $response->getBody(), true);
    }

    public function getClientID(): string
    {
        return $this->authResult['client_id'];
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getOAuthKey()
    {
        return $this->oAuthKey;
    }


    private function getHeaders()
    {
        return [
            'Authorization' => "{$this->oAuthKey}",
            'Content-Type' => 'application/json'
        ];
    }

    private function getResponse($endpoint, $body)
    {
        $response = $this->client->post($endpoint, ['body' => $body, 'headers' => $this->getHeaders()]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getAddressByPosition($latitude, $longitude)
    {
        $body = [
            'results' => 1,
            'skip' => 0,
            'what' => '',
            'll' => [$latitude, $longitude]
        ];
        $responseBody = $this->getResponse('client-api/3.0/geosearch', json_encode($body))['objects'];
        return count($responseBody) ? new Address($responseBody[0]) : null;
    }

    public function suggestAddresses(string $address, float $longitude, float $latitude)
    {
        $body = [
            'results' => 10,
            'skip' => 0,
            'what' => $address,
            'll' => $latitude && $longitude ? [$longitude, $latitude] : null
        ];
        return $this->getResponse('client-api/3.0/geosearch', json_encode($body))['objects'];
    }

    public function getNearestPickupPoint(float $longitude, float $latitude, $isStart = true)
    {
        $body = [
            'll' => [$longitude, $latitude],
            'dx' => 100,
            'type' => $isStart ? 'a' : 'b',
            'not_sticky' => true
        ];
        $geoObject = $this->getResponse('client-api/3.0/nearestposition', json_encode($body));
        $convertBody = ['ll' => $geoObject['point'], 'result' => 1];
        if (isset($geoObject['short_text'])) {
            $convertBody['what'] = $geoObject['short_text'];
        }
        if (isset($geoObject['uri'])) {
            $convertBody['uri'] = $geoObject['uri'];
        }
        if (isset($geoObject['point'])) {
            $convertBody['ll'] = $geoObject['point'];
        }
        return $this->getResponse('client-api/3.0/geosearch', json_encode($convertBody))['objects'][0];
    }

    public function findAddress(string $address, float $longitude, float $latitude)
    {
        return $this->suggestAddresses($address, $longitude, $latitude)[0];
    }

    private function mapClientGeoPointToServer(array $geopoint)
    {
        $result = array();
        $mapping = [
            "country" => 'country',
            "fullname" => 'full_text',
            "short_text" => 'short_text',
            "short_text_from" => 'short_text_from',
            "short_text_to" => 'short_text_to',
            "geopoint" => 'point',
            "locality" => 'city',
            "porchnumber" => 'porchnumber',
            "premisenumber" => 'house',
            "thoroughfare" => 'street',
            "flight" => 'flight',
            "terminal" => 'terminal',
            "type" => 'type',
            "object_type" => 'object_type'
        ];

        foreach ($mapping as $key => $value) {
            if (isset($geopoint[$value])) {
                $result[$key] = $geopoint[$value];
            }
        }
        return $result;
    }

    private function findEstimatePrice(array $response)
    {
        $service_level = $response['service_levels'][0];
        if (isset($service_level['price_raw'])) {
            return $service_level['price_raw'];
        }
        return null;
    }

    public function estimate(EstimateParams $params): Estimate
    {
        $body = [
            'route' => array_map(function ($point) {
                return [$point->getAddress()->getLatitude(), $point->getAddress()->getLongitude()];
            }, $params->getPoints()),
            'selected_class' => $params->getTariff(),
            'requirements' => $this->mapClientRequirementsToServer($params->getRequirements()),
            'client_id' => $this->getClientID(),
            'phone' => $params->getPhone(),
        ];
        $bodyString = json_encode($body);
        $response = $this->getResponse('api/1.0/estimate', $bodyString);
        $isFixed = isset($response['is_fixed_price']) ? $response['is_fixed_price'] : false;
        $offerID = isset($response['offer']) ? $response['offer'] : null;
        return new Estimate($params, $this->findEstimatePrice($response), $isFixed, $offerID);
    }

    private function mapClientBodyToServer($body): array
    {
        if (!count(array_keys($body['requirements']))) {
            $body['requirements'] = new stdClass();
        }
        return $body;
    }

    public function makeOrderRequest(array $order): string
    {
        $clientID = $this->getClientID();
        try {
            $path = "api/1.0/client/{$clientID}/order";
            $createResponse = $this->getResponse($path, json_encode($this->mapClientBodyToServer($order)));
            return (string) $createResponse['_id'];
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                var_dump(Psr7\str($e->getResponse()));
                $json = json_decode((Psr7\str($e->getResponse())));
                var_dump($json);
            }
            throw $e;
        }
    }



    private function mapRoutePointToServer(RoutePoint $point): array
    {
        $result = $this->mapClientGeoPointToServer($point->getAddress()->getArray());
        if ($point->getFloor()) {
            $result['floor'] = $point->getFloor();
        }
        if ($point->getComment()) {
            $result['comment'] = $point->getComment();
        }
        if ($point->getApartment()) {
            $result['apartment'] = $point->getApartment();
        }
        if ($point->getPhone()) {
            $result['phone'] = $point->getPhone();
        }
        return $result;
    }

    private function mapClientRequirementsToServer(array $requirements)
    {
        if (!count($requirements)) {
            return new stdClass();
        }
        $result = [];
        foreach ($requirements as $requirement) {
            $result[$requirement->getName()] = $requirement->getValue();
        }
        return $result;
    }

    public function createOrder(Order $order)
    {
        $destination = $order->getDestinations()[count($order->getDestinations()) - 1];
        $interimDestinations = array_slice($order->getDestinations(), 0, count($order->getDestinations()) - 1);
        $makeOrderParams = [
            'phone' => $order->getPhone(),
            'class' => $order->getTariff(),
            'source' => $this->mapRoutePointToServer($order->getSource()),
            'destination' => $this->mapRoutePointToServer($destination),
            'interim_destinations' => array_map([$this, 'mapRoutePointToServer'], $interimDestinations),
            'requirements' => $this->mapClientRequirementsToServer($order->getRequirements()),
        ];
        if ($order->getOfferID()) {
            $makeOrderParams['offer'] = $order->getOfferID();
        }
        return $this->makeOrderRequest($makeOrderParams);
    }

    public function commitOrder(string $orderID): void
    {
        $clientID = $this->getClientID();
        try {
            $path = "api/1.0/client/{$clientID}/order/${orderID}/processing";
            $this->getResponse($path, json_encode(new stdClass()));
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                var_dump(Psr7\str($e->getResponse()));
            }
            throw $e;
        }
    }
}
