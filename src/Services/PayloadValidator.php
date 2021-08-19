<?php


namespace App\Services;


use App\Services\Strategies\ValidationStrategy;

class PayloadValidator
{

    private array $requestContent;

    private array $errors = [];

    private ValidationStrategy $validationStrategy;

    public function isRequestValidJson(string $json): bool
    {
        if(gettype(json_decode($json, true)) === 'array') {
            $this->requestContent = json_decode($json, true);
            return true;
        }
        return false;
    }
    public function allRequiredFieldsPassed(array $requireds): bool
    {
        foreach ($requireds as $requiredField) {
            $fieldNameExist = $this->offsetExistenceCheck($requiredField);
            if (!$fieldNameExist) {
                $this->errors[] = "required field " . $requiredField . " is missing";
                return false;
            }
        }
        return true;
    }

    public function offsetExistenceCheck($fieldName): bool
    {
        if(!key_exists($fieldName, $this->requestContent)) {
            return false;
        }
        return true;
    }


    /**
     * @return array
     */
    public function getRequestContent(): array
    {
        return $this->requestContent;
    }
    public function getErrors(): array
    {
        return $this->errors;
    }
}