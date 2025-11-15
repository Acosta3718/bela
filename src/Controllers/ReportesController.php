<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Reporte;

class ReportesController extends Controller
{
    protected Reporte $reporte;

    public function __construct()
    {
        $this->reporte = new Reporte();
    }

    public function ganancias()
    {
        $inicio = Request::get('inicio', date('Y-m-01'));
        $fin = Request::get('fin', date('Y-m-t'));
        $ganancias = $this->reporte->ganancias($inicio, $fin);
        return $this->view('reportes/ganancias', compact('ganancias', 'inicio', 'fin'));
    }

    public function pagosFuncionarios()
    {
        $inicio = Request::get('inicio', date('Y-m-01'));
        $fin = Request::get('fin', date('Y-m-t'));
        $pagos = $this->reporte->pagosFuncionarios($inicio, $fin);
        return $this->view('reportes/pagos', compact('pagos', 'inicio', 'fin'));
    }
}