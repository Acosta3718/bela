<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Cita;
use App\Models\Funcionario;
use App\Models\Venta;

class DashboardController extends Controller
{
    public function index()
    {
        // AGREGAR VALIDACIÓN DE AUTENTICACIÓN
        if (!Auth::check()) {
            return $this->redirect('/login');
        }
        $usuario = Auth::user();
        $citas = (new Cita())->all();
        $funcionarios = (new Funcionario())->all();
        $ventas = (new Venta())->all();

        return $this->view('dashboard/index', [
            'usuario' => $usuario,
            'totalCitas' => count($citas),
            'totalFuncionarios' => count($funcionarios),
            'totalVentas' => count($ventas)
        ]);
    }
}