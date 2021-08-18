<?php

namespace App\Services\Strategies;

class SmallerThanOrEqualStrategy implements ValidationStrategy
{
    private string $condition;
    private string $message;

    public function __construct($condition)
    {
        $this->condition = $condition;
    }

    public function validate($fieldValue, $name): bool
    {
        if ($fieldValue < $this->condition)
            return true;
        $this->message = "$name must be smaller or equal $this->condition";
        return false;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

}