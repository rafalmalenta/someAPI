<?php

namespace App\Services\Strategies;

interface ValidationStrategy
{
    public function validate(string $fieldValue,string $name): bool;
    public function getMessage(): string;

}