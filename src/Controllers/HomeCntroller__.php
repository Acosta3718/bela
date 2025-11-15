<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index(): string
    {
        return $this->render('home/index', [
            'title' => 'Inicio',
            'message' => 'Bienvenido a la aplicación de anotaciones'
        ]);
    }
}