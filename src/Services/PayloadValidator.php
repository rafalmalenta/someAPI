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
            switch ($conditionName){
                case "shorterThanOrEqual":
                    if(!$this->shorterThanOrEqual($fieldName, $conditionDetails['value']))
                        $this->errors[] = $fieldName." has to be maximum ".$conditionDetails['value']." character long";
                    break;
                case "longerThanOrEqual":
                    if(!$this->longerThanOrEqual($fieldName, $conditionDetails['value']))
                        $this->errors[] = $fieldName." has to be minimum ".$conditionDetails['value']." character long";
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

    public function longerThanOrEqual($fieldName, $value): bool
    {
        return mb_strlen($this->requestContent["$fieldName"]) >= $value;
    }
    public function shorterThanOrEqual($fieldName, $value): bool
    {
        return mb_strlen($this->requestContent["$fieldName"]) <= $value;
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