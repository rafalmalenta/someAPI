<?php

namespace App\Services\Strategies;



class ShorterOrEqualStrategy implements ValidationStrategy
{
    private string $condition;
    private string $message;

    public function __construct($condition)
    {
        $this->condition = $condition;
    }

    public function validate($fieldValue, $name): bool
    {
        if (mb_strlen($fieldValue) <= $this->condition)
            return true;
        $this->message = "$name should have maximum $this->condition characters";
        return false;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
