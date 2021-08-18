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
//    public function validateField(string $fieldName, array $conditions)
//    {
//        foreach ($conditions as $conditionName => $conditionDetails) {
//
//            switch ($conditionName) {
//                case "shorterThanOrEqual":
//                    $this->shorterThanOrEqual($fieldName, $conditionDetails['value']);
//                    break;
//                case "longerThanOrEqual":
//                    $this->longerThanOrEqual($fieldName, $conditionDetails['value']);
//                    break;
//                case "greaterThanOrEqual":
//                    $this->greaterThanOrEqual($fieldName, $conditionDetails['value']);
//                    break;
//                case "smallerThanOrEqual":
//                    $this->smallerThanOrEqual($fieldName, $conditionDetails['value']);
//                    break;
//                case "regEx":
//                    if (!$this->regEx($fieldName, $conditionDetails['value']))
//                        $this->errors[] = $conditionDetails['msg'];
//                    break;
//                case "passwordCheck":
//                    if($this->offsetExistenceCheck("password") and $this->offsetExistenceCheck("password2"))
//                        if ($this->requestContent["password"] !== $this->requestContent["password2"])
//                            $this->errors[] = "passwords doesnt match";
//                    break;
//                case "ifExistValidate":
//                    if($this->offsetExistenceCheck("email"))
//                     if (!$this->regEx($fieldName, $conditionDetails['value']))
//                         $this->errors[] = $conditionDetails['msg'];
//            }
//        }
//    }

//    public function regEx($fieldName, $value): bool
//    {
//        if(!$this->offsetExistenceCheck($fieldName)) {
//            $this->errors[] = $fieldName ." is required";
//            return false;
//        }
//        if (!preg_match($value, $this->requestContent["$fieldName"]))
//            return false;
//        return true;
//    }
//    public function smallerThanOrEqual($fieldName, $value): void
//    {
//        if(!$this->offsetExistenceCheck($fieldName)) {
//            $this->errors[] = $fieldName ." is required";
//            return;
//        }
//        if ($this->requestContent["$fieldName"] > $value)
//            $this->errors[] = $fieldName . " has to be maximum " .$value;
//    }
//    public function greaterThanOrEqual($fieldName, $value): void
//    {
//        if(!$this->offsetExistenceCheck($fieldName)) {
//            $this->errors[] = $fieldName ." is required";
//            return;
//        }
//        if ($this->requestContent["$fieldName"] < $value)
//            $this->errors[] = $fieldName . " has to be minimum " .$value;
//    }
//    public function longerThanOrEqual($fieldName, $value): void
//    {
//        if(!$this->offsetExistenceCheck($fieldName)) {
//            $this->errors[] = $fieldName ." is required";
//            return;
//        }
//        if (mb_strlen($this->requestContent["$fieldName"]) <= $value)
//            $this->errors[] = $fieldName . " has to be minimum " .$value. " character long";
//    }
//    public function shorterThanOrEqual($fieldName, $value): void
//    {
//        if(!$this->offsetExistenceCheck($fieldName)) {
//            $this->errors[] = $fieldName ." is required";
//            return;
//        }
//        if (mb_strlen($this->requestContent["$fieldName"]) >= $value)
//            $this->errors[] = $fieldName . " has to be maximum " .$value. " character long";
//    }
//
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