<?php

namespace YandexTaxiCorpClient;

interface IRequirement
{
    public function getName(): string;
    public function getValue();
}
