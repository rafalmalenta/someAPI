<?php

namespace App\Services\Strategies;

class RegExStrategy implements ValidationStrategy
{
    private string $condition;
    private string $message;

    public function __construct($condition)
    {
        $this->condition = $condition;
    }

    public function validate($fieldValue, $name): bool
    {
        if (preg_match($this->condition,$fieldValue))
            return true;
        $this->message = "$name does not match pattern ";
        return false;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}