<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Cita;
use App\Models\Funcionario;
use App\Models\Venta;
use DateTimeImmutable;

class DashboardController extends Controller
{
    public function index()
    {
        /*// AGREGAR VALIDACIÓN DE AUTENTICACIÓN
        if (!Auth::check()) {
            return $this->redirect('/login');
        }*/
        $usuario = Auth::user();
        $citas = (new Cita())->all();
        $funcionarios = (new Funcionario())->all();
        $ventas = (new Venta())->all();

        $citaModel = new Cita();
        $funcionarioModel = new Funcionario();
        $ventaModel = new Venta();

        $totalCitas = $citaModel->countByStatuses(['pendiente', 'confirmada']);
        $funcionariosActivos = $funcionarioModel->activos();
        $totalFuncionarios = count($funcionariosActivos);
        $totalVentas = count($ventaModel->all());

        $hoy = new DateTimeImmutable('today');
        $fechaHoy = $hoy->format('Y-m-d');
        $disponibilidadHoy = [];

        foreach ($funcionariosActivos as $funcionario) {
            $disponibilidadHoy[] = [
                'funcionario' => $funcionario,
                'bloques' => $citaModel->bloquesDisponiblesDelDia((int)$funcionario['id'], $fechaHoy),
            ];
        }

        return $this->view('dashboard/index', [
            'usuario' => $usuario,
            'totalCitas' => count($citas),
            'totalFuncionarios' => count($funcionarios),
            'totalVentas' => count($ventas),
            'totalCitas' => $totalCitas,
            'totalFuncionarios' => $totalFuncionarios,
            'totalVentas' => $totalVentas,
            'disponibilidadHoy' => $disponibilidadHoy,
            'fechaHoy' => $hoy->format('d/m/Y'),
        ]);
    }
}