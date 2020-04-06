<?php

namespace YandexTaxiCorpClient;

/**
 * Адрес, полученный из геокодера.
 */
class Address
{
    private array $address;

    public function __construct(array $address)
    {
        $this->address = $address;
    }

    public function getArray()
    {
        return $this->address;
    }

    public function getLatitude()
    {
        return $this->address['point'][0];
    }

    public function getLongitude()
    {
        return $this->address['point'][1];
    }
}
