<?php

class Validator {
    public $data;
    public $error_messages = [];
    private $fields = [];
    private $currentField = null;

    public function __construct($data) {
        $this->data = $data;
    }

    public function field($name, $label = null) {
        $this->currentField = $name;
        $this->fields[$name] = [
            'label' => $label ?? $name,
            'rules' => []
        ];
        return $this;
    }

    public function required() {
        $this->fields[$this->currentField]['rules'][] = function($value, $label) {
            if (empty($value)) {
                return "$label är obligatoriskt.";
            }
            return null;
        };
        return $this;
    }

    public function email() {
        $this->fields[$this->currentField]['rules'][] = function($value, $label) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return "Ange en giltig e-postadress.";
            }
            return null;
        };
        return $this;
    }

    public function min_len($min) {
        $this->fields[$this->currentField]['rules'][] = function($value, $label) use ($min) {
            if (strlen($value) < $min) {
                return "$label måste vara minst $min tecken.";
            }
            return null;
        };
        return $this;
    }

    public function must_contain($pattern) {
        $this->fields[$this->currentField]['rules'][] = function($value, $label) use ($pattern) {
            if ($pattern === '0-9' && !preg_match('/[0-9]/', $value)) {
                return "$label måste innehålla minst en siffra.";
            }
            // Lägg till fler mönster vid behov
            return null;
        };
        return $this;
    }

    public function equals($otherValue) {
        $this->fields[$this->currentField]['rules'][] = function($value, $label) use ($otherValue) {
            if ($value !== $otherValue) {
                return "$label måste matcha.";
            }
            return null;
        };
        return $this;
    }

    public function is_valid() {
        $this->error_messages = [];
        foreach ($this->fields as $name => $field) {
            $value = $this->data[$name] ?? '';
            foreach ($field['rules'] as $rule) {
                $error = $rule($value, $field['label']);
                if ($error) {
                    $this->error_messages[$name] = $error;
                    break; // Visa bara första felet per fält
                }
            }
        }
        return empty($this->error_messages);
    }
}