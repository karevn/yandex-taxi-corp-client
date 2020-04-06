<?php

namespace YandexTaxiCorpClient;

interface IEstimateParams
{
    public function getPoints(): array;
    public function getTariff(): string;
    public function getRequirements(): array;
    public function getPhone(): string;
}

/**
 * Параметры для оценки поездки
 */
class EstimateParams implements IEstimateParams
{
    private array $points;
    private string $tariff;
    private array $requirements;
    private string $phone;

    function __construct(string $phone, array $points, string $tariff, array $requirements)
    {
        $this->points = $points;
        $this->tariff = $tariff;
        $this->requirements = $requirements;
        $this->phone = $phone;
    }

    public function getPoints(): array
    {
        return $this->points;
    }

    public function getTariff(): string
    {
        return $this->tariff;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }
    public function getPhone(): string
    {
        return $this->phone;
    }
};
