<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [])
    {
        return View::make($template, $data);
    }

    protected function redirect(string $path)
    {
        redirect($path);
    }
}