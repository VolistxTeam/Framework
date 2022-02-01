<?php

namespace App\ValidationRules;

abstract class ValidationRuleBase
{
    protected array $inputs;

    public function __construct(array $inputs)
    {
        $this->inputs = $inputs;
    }

    abstract public function Validate(): bool|array;
}