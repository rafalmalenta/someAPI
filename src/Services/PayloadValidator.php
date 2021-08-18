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
    public function validate(string $fieldName, bool $required, array $strategyArray): bool
    {
        $fieldNameExist = $this->offsetExistenceCheck($fieldName);
        if(!$fieldNameExist && $required)
        {
            $this->errors[] = "required field ".$fieldName." is missing";
            return false;
        }
        $localErrorsCount = 0;
        foreach ($strategyArray as $strategy) {
            $this->setStrategy($strategy);
            if(!$this->validationStrategy->validate($this->requestContent["$fieldName"], $fieldName)) {
                $this->errors[] = $this->validationStrategy->getMessage();
                $localErrorsCount++;
            }
        }
        if($localErrorsCount === 0)
            return true;
        return false;

    }
    public function setStrategy(ValidationStrategy $strategy)
    {
        $this->validationStrategy = $strategy;
    }
    public function passwordsMatch()
    {
        if($this->offsetExistenceCheck("password") and $this->offsetExistenceCheck("password2"))
            if($this->requestContent['password']===$this->requestContent['password2'])
                return true;
        $this->errors[] = "passwords are not the same";
    }
    public function offsetExistenceCheck($fieldName): bool
    {
        if(!key_exists($fieldName, $this->requestContent)) {
            return false;
        }
        return true;
    }
    public function allIsGood(): bool
    {
        return count($this->errors) === 0;
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