<?php

namespace App\Core;

class Request
{
    public static function all(): array
    {
        return self::sanitize($_POST);
    }

    public static function get(string $key, $default = null)
    {
        $value = $_GET[$key] ?? $default;

        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            return self::sanitize($value);
        }

        return $value;
    }

    protected static function sanitize($value)
    {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $item) {
                $sanitized[$key] = self::sanitize($item);
            }
            return $sanitized;
        }

        return is_string($value) ? trim($value) : $value;
    }
}