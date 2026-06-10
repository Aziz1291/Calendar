<?php
namespace App;

class Validator
{
    private $data;
    protected $errors;
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    /**
     * Summary of validates
     * @param array $data
     * @return array|bool
     */
    public function validates(array $data): array
    {
        $this->data = $data;
        $this->errors = [];
        return $this->errors;
    }
    public function validate(string $field, string $method, ...$parameters): bool
    {
        if (!isset($this->data[$field])) {
            $this->errors[$field] = "Field {$field} is required";
            return false;
        } else {
            return call_user_func([$this, $method], $field, ...$parameters);
        }
    }
    public function minLength(string $field, int $length)
    {
        if (mb_strlen($field) < $length) {
            $this->errors[$field] = "Field {$field} must be at least {$length} characters long";
            return false;
        }
        return true;
    }
    public function date(string $field)
    {
        if (\DATETIME::createFromFormat('Y-m-d', $this->data[$field]) === false) {
            $this->errors[$field] = "Field {$field} is not a valid date";
            return false;
        }
        return true;
    }
    public function time(string $field)
    {
        if (\DATETIME::createFromFormat('H:i', $this->data[$field]) === false) {
            $this->errors[$field] = "Field {$field} is not a valid time";
            return false;
        }
        return true;
    }
    public function beforeTime(string $startField, string $endField)
    {
        if ($this->time($startField) && $this->time($endField)) {
            $start = \DATETIME::createFromFormat('H:i', $this->data[$startField]);
            $end = \DATETIME::createFromFormat('H:i', $this->data[$endField]);
            if ($start->getTimestamp() > $end->getTimestamp()) {
                $this->errors[$startField] = "Field {$startField} must be before {$endField}";
                return false;
            }
            return true;
        }
        return false;
    }
}