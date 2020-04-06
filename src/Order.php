<?php

namespace YandexTaxiCorpClient;

/**
 * Заказ. Создаётся напрямую или из экземпляра Estimate статическим методом createEstimate.
 */
class Order
{
    private string $phone;
    private RoutePoint $source;
    private array $destinations;
    private array $requirements;
    private string $tariff;
    private Estimate $estimate;

    public static function createFromEstimate(Estimate $estimate)
    {
        $params = $estimate->getParams();
        $source = $params->getPoints()[0];
        $destinations = array_slice($params->getPoints(), 1, count($params->getPoints()) - 1);
        return new Order($params->getPhone(), $source, $destinations, $params->getRequirements(), $params->getTariff(), $estimate);
    }

    public function __construct(string $phone, RoutePoint $source, array $destinations, array $requirements, string $tariff, Estimate $estimate)
    {
        $this->phone = $phone;
        $this->source = $source;
        $this->destinations = $destinations;
        $this->tariff = $tariff;
        if ($requirements) {
            $this->requirements = $requirements;
        }
        if ($estimate) {
            $this->estimate = $estimate;
        }
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getDestinations()
    {
        return $this->destinations;
    }

    public function getRequirements()
    {
        return isset($this->requirements) ? $this->requirements : [];
    }

    public function getOfferID()
    {
        return $this->estimate ? $this->estimate->getOfferID() : null;
    }

    public function getTariff()
    {
        return $this->tariff;
    }
}
