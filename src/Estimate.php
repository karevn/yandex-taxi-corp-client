<?php

namespace YandexTaxiCorpClient;

/**
 * Оценка поездки. Экземпляры этого класса создаются внутри вызова estimate объекта Client.
 * Не предназначена для самостоятельного создания, 
 */
class Estimate
{
    private EstimateParams $params;
    private float $price;
    private bool $isFixed;
    private string $offerID;

    function __construct(EstimateParams $params, float $price, bool $isFixed, string $offerID)
    {
        $this->params = $params;
        $this->price = $price;
        $this->isFixed = $isFixed;
        if ($offerID) {
            $this->offerID = $offerID;
        }
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getIsFixed()
    {
        return $this->isFixed;
    }

    public function getOfferID()
    {
        return $this->offerID;
    }
}
