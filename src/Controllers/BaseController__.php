<?php

namespace App\Controllers;

class BaseController
{
    protected string $layout = 'layouts/main';

    protected function render(string $view, array $params = []): string
    {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (!is_readable($viewFile)) {
            throw new \RuntimeException("Vista {$view} no encontrada");
        }

        extract($params, EXTR_OVERWRITE);
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../Views/' . $this->layout . '.php';
        if (!is_readable($layoutFile)) {
            return $content;
        }

        extract($params, EXTR_SKIP);
        ob_start();
        include $layoutFile;
        return (string) ob_get_clean();
    }
}