<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Funcionario;
use App\Models\Venta;

class VentasController extends Controller
{
    protected Venta $model;
    protected Cita $cita;
    protected Funcionario $funcionario;
    protected Cliente $cliente;
    protected Cuenta $cuenta;

    public function __construct()
    {
        $this->model = new Venta();
        $this->cita = new Cita();
        $this->funcionario = new Funcionario();
        $this->cliente = new Cliente();
        $this->cuenta = new Cuenta();
    }

    public function index()
    {
        $hoy = date('Y-m-d');
        $fechaIni = Request::get('fecha_ini') ?: $hoy;
        $fechaFin = Request::get('fecha_fin') ?: $fechaIni;
        $clienteId = Request::get('cliente_id');
        $clienteId = $clienteId !== null && $clienteId !== '' ? (int)$clienteId : null;

        if ($fechaIni > $fechaFin) {
            [$fechaIni, $fechaFin] = [$fechaFin, $fechaIni];
        }

        $ventas = $this->model->listarConDetalles($fechaIni, $fechaFin, $clienteId);
        $citaIds = array_values(array_filter(array_column($ventas, 'cita_id')));
        $serviciosPorCita = $this->cita->serviciosConPrecioPorCita($citaIds);
        $citasInfo = $this->cita->infoBasicaPorIds($citaIds);
        $cuentas = $this->cuenta->activos();
        $clienteSeleccionado = $clienteId ? $this->cliente->find($clienteId) : null;
        $clienteLabel = '';

        if ($clienteSeleccionado) {
            $detalles = array_filter([
                $clienteSeleccionado['telefono'] ?? '',
                $clienteSeleccionado['email'] ?? '',
            ]);
            $clienteLabel = trim($clienteSeleccionado['nombre'] . (!empty($detalles) ? ' · ' . implode(' · ', $detalles) : ''));
        }

        return $this->view('ventas/index', compact(
            'ventas',
            'serviciosPorCita',
            'citasInfo',
            'fechaIni',
            'fechaFin',
            'cuentas',
            'clienteId',
            'clienteLabel'
        ));
    }

    public function create()
    {
        $clienteId = Request::get('cliente_id');
        $clienteId = $clienteId !== null && $clienteId !== '' ? (int)$clienteId : null;
        $clienteLabel = '';
        if ($clienteId) {
            $cliente = $this->cliente->find($clienteId);
            if ($cliente) {
                $detalles = array_filter([
                    $cliente['telefono'] ?? '',
                    $cliente['email'] ?? '',
                ]);
                $clienteLabel = trim($cliente['nombre'] . (!empty($detalles) ? ' · ' . implode(' · ', $detalles) : ''));
            }
        }
        $citas = $this->cita->citasConTotales($clienteId);
        $serviciosPorCita = $this->cita->serviciosConPrecioPorCita(array_column($citas, 'id'));
        $cuentas = $this->cuenta->activos();
        return $this->view('ventas/create', compact('citas', 'clienteId', 'serviciosPorCita', 'clienteLabel', 'cuentas'));
    }

    public function store()
    {
        $data = Request::all();
        $citaIds = array_values(array_filter((array)($data['cita_ids'] ?? []), fn($id) => $id !== ''));
        $citaIds = array_values(array_unique(array_map('intval', $citaIds)));

        $errors = Validator::validate($data, [
            'descuento' => 'required',
        ]);

        if (empty($citaIds)) {
            $errors['cita_ids'][] = 'Seleccione al menos una cita';
        }

        $cuentaId = isset($data['cuenta_id']) ? (int)$data['cuenta_id'] : null;
        $requiereCuenta = !empty($data['cobrar']) && (int)$data['cobrar'] === 1;
        $cuentaSeleccionada = $cuentaId ? $this->cuenta->findActiva($cuentaId) : null;

        if ($requiereCuenta && !$cuentaSeleccionada) {
            $errors['cuenta_id'][] = 'Seleccione una cuenta activa para registrar el cobro.';
        }

        $descuento = (float)($data['descuento'] ?? 0);
        $serviciosPorCita = $this->cita->serviciosConPrecioPorCita($citaIds);
        $subtotal = 0;
        foreach ($serviciosPorCita as $detalles) {
            foreach ($detalles as $detalle) {
                $subtotal += (float)($detalle['precio_base'] ?? 0);
            }
        }

        $montoTotal = max(0, $subtotal - $descuento);

        if ($errors) {
            $citas = $this->cita->citasConTotales();
            $venta = $data;
            $venta['cita_ids'] = $citaIds;
            $cuentas = $this->cuenta->activos();
            return $this->view('ventas/create', compact('errors', 'venta', 'citas', 'serviciosPorCita', 'cuentas'));
        }

        $data['cita_id'] = $citaIds[0] ?? null;
        $data['monto_total'] = $montoTotal;
        $data['monto_pagado'] = isset($data['cobrar']) && $data['cobrar'] ? $montoTotal : (float)($data['monto_pagado'] ?? 0);
        $data['estado_pago'] = isset($data['cobrar']) && $data['cobrar'] ? 'pagado' : ($data['estado_pago'] ?? 'pendiente');
        $data['cuenta_id'] = $requiereCuenta ? $cuentaId : null;

        $ventaId = $this->model->create($data);

        if ($requiereCuenta && $cuentaSeleccionada) {
            $this->cuenta->depositar($cuentaId, $montoTotal);
        }
        return $this->redirect('/ventas');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $venta = $this->model->find($id);
        $venta['cita_ids'] = [$venta['cita_id'] ?? null];
        $citas = $this->cita->citasConTotales(null, $venta['id']);
        $serviciosPorCita = $this->cita->serviciosConPrecioPorCita(array_column($citas, 'id'));
        $cuentas = $this->cuenta->activos();
        return $this->view('ventas/edit', compact('venta', 'citas', 'serviciosPorCita', 'cuentas'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $citaIds = array_values(array_filter((array)($data['cita_ids'] ?? []), fn($id) => $id !== ''));
        $citaIds = array_values(array_unique(array_map('intval', $citaIds)));

        $errors = Validator::validate($data, [
            'descuento' => 'required',
        ]);

        if (empty($citaIds)) {
            $errors['cita_ids'][] = 'Seleccione al menos una cita';
        }

        $cuentaId = isset($data['cuenta_id']) ? (int)$data['cuenta_id'] : null;
        $requiereCuenta = !empty($data['cobrar']) && (int)$data['cobrar'] === 1;
        $cuentaSeleccionada = $cuentaId ? $this->cuenta->findActiva($cuentaId) : null;

        if ($requiereCuenta && !$cuentaSeleccionada) {
            $errors['cuenta_id'][] = 'Seleccione una cuenta activa para registrar el cobro.';
        }

        $descuento = (float)($data['descuento'] ?? 0);
        $serviciosPorCita = $this->cita->serviciosConPrecioPorCita($citaIds);
        $subtotal = 0;
        foreach ($serviciosPorCita as $detalles) {
            foreach ($detalles as $detalle) {
                $subtotal += (float)($detalle['precio_base'] ?? 0);
            }
        }

        $montoTotal = max(0, $subtotal - $descuento);

        if ($errors) {
            $venta = array_merge($data, ['id' => $id]);
            $venta['cita_ids'] = $citaIds;
            $citas = $this->cita->citasConTotales(null, $id);
            $cuentas = $this->cuenta->activos();
            return $this->view('ventas/edit', compact('errors', 'venta', 'citas', 'serviciosPorCita', 'cuentas'));
        }

        $data['cita_id'] = $citaIds[0] ?? null;
        $data['monto_total'] = $montoTotal;
        $data['monto_pagado'] = isset($data['cobrar']) && $data['cobrar'] ? $montoTotal : (float)($data['monto_pagado'] ?? 0);
        $data['estado_pago'] = isset($data['cobrar']) && $data['cobrar'] ? 'pagado' : ($data['estado_pago'] ?? 'pendiente');
        $data['cuenta_id'] = $requiereCuenta ? $cuentaId : ($data['cuenta_id'] ?? null);

        $this->model->update($id, $data);

        if ($requiereCuenta && $cuentaSeleccionada) {
            $this->cuenta->depositar($cuentaId, $montoTotal);
        }
        return $this->redirect('/ventas');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/ventas');
    }

    public function registrarCobro()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $monto = (float)$data['monto'];
        $cuentaId = isset($data['cuenta_id']) ? (int)$data['cuenta_id'] : null;
        $cuenta = $cuentaId ? $this->cuenta->findActiva($cuentaId) : null;

        if (!$cuenta || $monto <= 0) {
            return $this->redirect('/ventas');
        }

        $this->cuenta->depositar($cuentaId, $monto);

        $this->model->registrarCobro($id, $monto, $cuentaId);
        return $this->redirect('/ventas');
    }
}