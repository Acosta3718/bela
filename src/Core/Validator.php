<?php

namespace App\Core;

class Validator
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            foreach ($rulesArray as $rule) {
                if ($rule === 'required' && ($value === null || $value === '')) {
                    $errors[$field][] = 'Este campo es obligatorio';
                }
                if ($rule === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Formato de correo inválido';
                }
                if (str_starts_with($rule, 'max:')) {
                    $limit = (int)substr($rule, 4);
                    if ($value && strlen($value) > $limit) {
                        $errors[$field][] = "Debe tener máximo {$limit} caracteres";
                    }
                }
            }
        }
        return $errors;
    }
}