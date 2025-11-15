<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/helpers.php';

use App\Core\Router;

$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
if ($scriptDir === '/' || $scriptDir === '\\') {
    $scriptDir = '';
}

$basePath = rtrim($scriptDir, '/');
if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', $basePath);
}

$router = new Router($scriptDir);

require __DIR__ . '/../src/Routes/web.php';

echo $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);