<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Funcionario;
use App\Models\Pago;
use App\Models\Venta;

class PagosController extends Controller
{
    protected Pago $model;
    protected Funcionario $funcionario;
    protected Venta $venta;

    public function __construct()
    {
        $this->model = new Pago();
        $this->funcionario = new Funcionario();
        $this->venta = new Venta();
    }

    public function index()
    {
        $pagos = $this->model->all();
        $funcionarios = $this->funcionario->all();
        return $this->view('pagos/index', compact('pagos', 'funcionarios'));
    }

    public function create()
    {
        $funcionarios = $this->funcionario->activos();
        $funcionarioId = (int)Request::get('funcionario_id', 0);
        $periodoInicio = Request::get('periodo_inicio') ?: date('Y-m-d');
        $periodoFin = Request::get('periodo_fin') ?: date('Y-m-d');

        $ventas = [];
        if ($funcionarioId) {
            $ventas = $this->venta->pagadasParaPago($funcionarioId, $periodoInicio, $periodoFin);
        }

        return $this->view('pagos/create', compact('funcionarios', 'ventas', 'funcionarioId', 'periodoInicio', 'periodoFin'));
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'funcionario_id' => 'required',
            'fecha_pago' => 'required',
            'periodo_inicio' => 'required',
            'periodo_fin' => 'required'
        ]);

        $funcionarioId = (int)($data['funcionario_id'] ?? 0);
        $ventaIds = array_values(array_filter(array_map('intval', $data['venta_ids'] ?? [])));

        if (empty($ventaIds)) {
            $errors['venta_ids'][] = 'Seleccione al menos una venta pagada.';
        }

        if ($errors) {
            $funcionarios = $this->funcionario->activos();
            $ventas = $funcionarioId
                ? $this->venta->pagadasParaPago($funcionarioId, $data['periodo_inicio'] ?? null, $data['periodo_fin'] ?? null)
                : [];
            $pago = $data;
            return $this->view('pagos/create', compact('errors', 'pago', 'funcionarios', 'ventas'));
        }

        $ventasPagadas = $this->venta->pagadasParaPago($funcionarioId, $data['periodo_inicio'], $data['periodo_fin'], $ventaIds);

        if (empty($ventasPagadas)) {
            $errors['venta_ids'][] = 'No se encontraron ventas cobradas para el funcionario y rango indicado.';
            $funcionarios = $this->funcionario->activos();
            $ventas = [];
            $pago = $data;
            return $this->view('pagos/create', compact('errors', 'pago', 'funcionarios', 'ventas'));
        }

        foreach ($ventasPagadas as $venta) {
            $montoBase = (float)($venta['monto_pagado'] ?? $venta['monto_total']);
            $porcentaje = (float)($venta['porcentaje_comision'] ?? 0);
            $comision = $montoBase * ($porcentaje / 100);

            $this->model->create([
                'funcionario_id' => $funcionarioId,
                'venta_id' => $venta['id'],
                'monto' => $comision,
                'fecha_pago' => $data['fecha_pago'],
                'periodo_inicio' => $data['periodo_inicio'],
                'periodo_fin' => $data['periodo_fin'],
                'notas' => $data['notas'] ?? '',
            ]);
        }

        return $this->redirect('/pagos');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/pagos');
    }

    public function ventas()
    {
        $funcionarioId = (int)Request::get('funcionario_id', 0);
        $desde = Request::get('desde');
        $hasta = Request::get('hasta');

        if (!$funcionarioId) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            exit;
        }

        $ventas = $this->venta->pagadasParaPago($funcionarioId, $desde, $hasta);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_map(function ($venta) {
            $montoBase = (float)($venta['monto_pagado'] ?? $venta['monto_total']);
            $porcentaje = (float)($venta['porcentaje_comision'] ?? 0);
            $comision = $montoBase * ($porcentaje / 100);

            return [
                'id' => (int)$venta['id'],
                'cita_fecha' => $venta['cita_fecha'] ?? null,
                'monto_total' => $venta['monto_total'],
                'monto_pagado' => $venta['monto_pagado'],
                'porcentaje_comision' => $porcentaje,
                'comision' => $comision,
            ];
        }, $ventas));
        exit;
    }
}