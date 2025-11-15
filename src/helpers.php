<?php
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        static $loaded = false;
        if (!$loaded) {
            $path = __DIR__ . '/../.env';
            if (file_exists($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (str_starts_with(trim($line), '#')) {
                        continue;
                    }
                    [$name, $value] = array_map('trim', explode('=', $line, 2));
                    $_ENV[$name] = $value;
                }
            }
            $loaded = true;
        }
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = [])
    {
        return \App\Core\View::make($template, $data);
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        return defined('APP_BASE_PATH') && APP_BASE_PATH !== '' ? APP_BASE_PATH : '';
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = app_base_path();
        $path = trim($path);

        if ($path === '' || $path === '/') {
            return $base === '' ? '/' : $base;
        }

        $path = ltrim($path, '/');

        return $base === '' ? '/' . $path : $base . '/' . $path;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path)
    {
        if (!preg_match('/^https?:\/\//i', $path)) {
            $path = url($path);
        }

        header("Location: {$path}");
        exit;
    }
}