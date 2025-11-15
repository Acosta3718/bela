<?php

namespace App\Core;

class View
{
    public static function make(string $template, array $data = [])
    {
        $basePath = __DIR__ . '/../../resources/views/';
        $file = $basePath . str_replace('.', '/', $template) . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("View {$template} not found");
        }

        extract($data);
        ob_start();
        require $file;
        return ob_get_clean();
    }
}