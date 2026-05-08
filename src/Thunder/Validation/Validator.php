<?php

namespace Thunder\Validation;

use Thunder\Exceptions\ValidationException;

class Validator implements ValidatorInterface
{
    private array $errors = [];
    public function __construct(private readonly array $data){}
    public function validate(array $rules): void{
        foreach($rules as $field => $ruleString){
            foreach(explode('|', $ruleString) as $rule){
                $this->applyRule($field, $rule);
            }
        }
        if (!empty($this->errors)){
            throw new ValidationException('Validation failed');
        }
    }
    private function applyRule(string $field, string $rule): void{
        $value = $this->data[$field] ?? null;
        if ($rule === 'nullable' && $value === null) return; //skip remaining rules
        if($value === null && $rule !== 'required')  return; //null values skip non-required rules silently
        [$ruleName, $ruleParam] = array_pad(explode(':', $rule, 2), 2, null);

        match($ruleName) {
            'required'  => (!isset($this->data[$field]) || $this->data[$field] === '')
                && $this->addError($field, "The $field field is required"),

            'string'    => (!is_string($value))
                && $this->addError($field, "The $field must be a string"),

            'numeric'   => (!is_numeric($value))
                && $this->addError($field, "The $field must be a number"),

            'email'     => (!filter_var($value, FILTER_VALIDATE_EMAIL))
                && $this->addError($field, "The $field must be a valid email"),

            'min'       => (is_string($value) && strlen($value) < (int)$ruleParam
                    || is_numeric($value) && $value < (int)$ruleParam)
                && $this->addError($field, "The $field minimum is $ruleParam"),

            'max'       => (is_string($value) && strlen($value) > (int)$ruleParam
                    || is_numeric($value) && $value > (int)$ruleParam)
                && $this->addError($field, "The $field maximum is $ruleParam"),
            'integer'   => (!ctype_digit($value))
                && $this->addError($field, "The $field must be an integer"),
            'boolean' => (!is_bool($value))
                && $this->addError($field, "The $field must be a boolean"),
            'alpha'     => (!ctype_alpha($value))
                && $this->addError($field, "The $field must be a character"),
            'alpha_num' => (!ctype_alnum($value))
                && $this->addError($field, "The $field must be a character"),
            'url'       => (!filter_var($value, FILTER_VALIDATE_URL))
                && $this->addError($field, "The $field must be a valid URL"),
            'uuid'  =>  (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value))
                && $this->addError($field, "The $field must be a valid UUID"),
            'regex' => (!preg_match($ruleParam, $value))
                && $this->addError($field, "The $field must match $ruleParam"),
            'between' => (function() use ($value, $ruleParam) {
                    $params = explode(',', $ruleParam);
                    return $value < $params[0] || $value > $params[1];
                })() && $this->addError($field, "The $field must be between " . explode(',', $ruleParam)[0] . " and " . explode(',',
                        $ruleParam)[1]),
            'in' => (!in_array($value, explode(',', $ruleParam)))
                && $this->addError($field, "The $field must be one of: $ruleParam"),
            'not_in' => (in_array($value, explode(',', $ruleParam)))
                && $this->addError($field, "The $field must not be one of: $ruleParam"),
            'confirmed' => ($this->data["{$field}_confirmation"] ?? null) !== $value
                && $this->addError($field, "The $field must match $ruleName"),
            'date' => (strtotime($value) === false)
                && $this->addError($field, "The $field must be a valid date"),
            'before' => (strtotime($value) >= strtotime($ruleParam))
                && $this->addError($field, "The $field must be before $ruleParam"),
            'after' => (strtotime($value) <= strtotime($ruleParam))
                && $this->addError($field, "The $field must be after $ruleParam"),
            'positive' => (!is_numeric($value) || $value <= 0)
                && $this->addError($field, "The $field must be a positive number"),
            'negative' => (!is_numeric($value) || $value >= 0)
                && $this->addError($field, "The $field must be a negative number"),
            'array' => (!is_array($value))
                && $this->addError($field, "The $field must be an array"),
            default     => null
        };
    }

    private function addError(string $field, string $message):void
    {
        $this->errors[$field][] = $message;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}