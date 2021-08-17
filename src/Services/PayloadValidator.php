<?php


namespace App\Services;


class PayloadValidator
{
    private string $json;

    private array $requestContent;

    private array $errors = [];

    public function __construct(string $json)
    {
        $this->json = $json;
    }
    public function isRequestValidJson(): bool
    {
        if(gettype(json_decode($this->json, true)) === 'array') {
            $this->requestContent = json_decode($this->json, true);
            return true;
        }
        return false;
    }
    public function validateField(string $fieldName, array $conditions)
    {
        foreach ($conditions as $conditionName=>$conditionDetails)
            if(!key_exists($fieldName,$this->requestContent)){
                $this->errors[]="missing value";
                return;
            }
            switch ($conditionName){
                case "shorterThanOrEqual":
                    if(mb_strlen($this->requestContent[$fieldName]) >= $conditionDetails['value'])
                        $this->errors[] = $fieldName." has to be maximum ".$conditionDetails['value']." character long";
                    break;
                case "longerThanOrEqual":
                    if(mb_strlen($this->requestContent[$fieldName]) <= $conditionDetails['value'])
                        $this->errors[] = $fieldName." has to be minimum ".$conditionDetails['value']." character long";
                    break;
                case "greaterThanOrEqual":
                    if($this->requestContent[$fieldName] <= $conditionDetails['value'])
                        $this->errors[] = $fieldName." has to be minimum ".$conditionDetails['value'];
                    break;
                case "smallerThanOrEqual":
                    if($this->requestContent[$fieldName] >= $conditionDetails['value'])
                        $this->errors[] = $fieldName." has to be maximum ".$conditionDetails['value'];
                    break;
                case "regEx":
                    if(!preg_match($conditionDetails['value'], $this->requestContent["$fieldName"]))
                        $this->errors[] = $conditionDetails['msg'];
                    break;
                case "passwordCheck":
                    if($this->requestContent["password"] !== $this->requestContent["password2"])
                        $this->errors[] = "passwords doesnt match";

            }
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