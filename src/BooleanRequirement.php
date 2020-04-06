<?php

namespace YandexTaxiCorpClient;

/**
 * Требование типа да / нет. Требования с $value = false создавать не рекомендуется.
 */
class BooleanRequirement implements IRequirement
{
    private string $name;
    private $value;

    public function __construct(string $name, $value = true)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }
}
