<?php

namespace App\Core;

class Router
{
    protected array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function __construct(private string $basePath = '')
    {
        $this->basePath = '/' . trim($basePath, '/');
        if ($this->basePath === '/') {
            $this->basePath = '';
        }
    }

    public function get(string $uri, callable|array $action): void
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
    }

    public function post(string $uri, callable|array $action): void
    {
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    public function dispatch(string $method, string $uri)
    {
        // Obtener solo el path sin query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Remover el base path
        $uri = $this->stripBasePath($uri);
        
        // Normalizar
        if ($uri === '' || $uri === '/' || $uri === 'index.php' || $uri === '/index.php') {
            $uri = '/';
        } else {
            $uri = $this->normalize($uri);
        }
        
        $method = strtoupper($method);

        $action = $this->routes[$method][$uri] ?? null;
        
        if (!$action) {
            http_response_code(404);
            return View::make('errors/404', ['uri' => $uri]);
        }

        if (is_callable($action)) {
            return call_user_func($action);
        }

        [$controller, $method] = $action;
        
        // FIX: No concatenar namespace si ya lo tiene
        if (!str_contains($controller, '\\')) {
            $controller = "App\\Controllers\\{$controller}";
        }
        
        $instance = new $controller();

        return call_user_func([$instance, $method]);
    }

    /*public function dispatch(string $method, string $uri)
    {
        $uri = $this->stripBasePath(parse_url($uri, PHP_URL_PATH));

        if ($uri === '' || $uri === '/' || $uri === 'index.php' || $uri === '/index.php') {
            $uri = '/';
        } else {
            $uri = $this->normalize($uri);
        }
        $method = strtoupper($method);

        $action = $this->routes[$method][$uri] ?? null;
        if (!$action) {
            http_response_code(404);
            return View::make('errors/404', ['uri' => $uri]);
        }

        if (is_callable($action)) {
            return call_user_func($action);
        }

        [$controller, $method] = $action;

        // Si ya es un FQCN (tiene namespace), no concatenar
        if (!str_contains($controller, '\\')) {
            $controller = "App\\Controllers\\{$controller}";
        }

        $instance = new $controller();
        return call_user_func([$instance, $method]);
    }*/

    protected function normalize(string $uri): string
    {
        return '/' . trim($uri, '/');
    }

    private function stripBasePath(string $uri): string
    {
        if ($this->basePath && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        return $uri ?: '/';
    }
}