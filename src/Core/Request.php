<?php

namespace App\Core;

class Request
{
    public static function all(): array
    {
        return array_map('trim', $_POST);
    }

    public static function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}