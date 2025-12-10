<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Cobro;
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
    protected Cobro $cobro;

    public function __construct()
    {
        $this->model = new Venta();
        $this->cita = new Cita();
        $this->funcionario = new Funcionario();
        $this->cliente = new Cliente();
        $this->cuenta = new Cuenta();
        $this->cobro = new Cobro();
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
        $ventaIds = array_column($ventas, 'id');
        $totalesCobro = $this->cobro->totalesPorVenta($ventaIds);
        foreach ($ventas as &$venta) {
            $resumenCobro = $totalesCobro[$venta['id']] ?? ['total' => 0, 'cuenta_id' => null];
            $venta['monto_pagado'] = $resumenCobro['total'];
            $venta['estado_pago'] = $resumenCobro['total'] >= (float)$venta['monto_total'] ? 'pagado' : 'pendiente';
            if (!empty($resumenCobro['cuenta_id'])) {
                $venta['cuenta_id'] = $resumenCobro['cuenta_id'];
            }
        }
        unset($venta);
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
        $subtotalCalculado = 0;
        foreach ($serviciosPorCita as $detalles) {
            foreach ($detalles as $detalle) {
                $subtotalCalculado += (float)($detalle['precio_base'] ?? 0);
            }
        }

        $subtotalIngresado = isset($data['subtotal']) && $data['subtotal'] !== '' ? (float)$data['subtotal'] : null;
        $subtotal = $subtotalIngresado !== null ? max(0, $subtotalIngresado) : $subtotalCalculado;
        $montoTotal = max(0, $subtotal - $descuento);

        if ($errors) {
            $citas = $this->cita->citasConTotales();
            $venta = $data;
            $venta['cita_ids'] = $citaIds;
            $venta['subtotal'] = $data['subtotal'] ?? $subtotalCalculado;
            $cuentas = $this->cuenta->activos();
            return $this->view('ventas/create', compact('errors', 'venta', 'citas', 'serviciosPorCita', 'cuentas'));
        }

        $data['cita_id'] = $citaIds[0] ?? null;
        $data['monto_total'] = $montoTotal;
        $montoPagado = $requiereCuenta ? $montoTotal : 0;
        $data['monto_pagado'] = $montoPagado;
        $data['estado_pago'] = $montoPagado >= $montoTotal ? 'pagado' : ($data['estado_pago'] ?? 'pendiente');
        $data['cuenta_id'] = $requiereCuenta ? $cuentaId : null;

        $ventaId = $this->model->create($data);

        if ($requiereCuenta && $cuentaSeleccionada) {
            $this->cobro->registrar($ventaId, $cuentaId, $montoTotal);
            $this->cuenta->depositar($cuentaId, $montoTotal);
        }
        return $this->redirect('/ventas');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $venta = $this->model->find($id);
        $resumenCobro = $this->cobro->totalesPorVenta([$id])[$id] ?? ['total' => 0, 'cuenta_id' => $venta['cuenta_id'] ?? null];
        $venta['monto_pagado'] = $resumenCobro['total'];
        $venta['cuenta_id'] = $resumenCobro['cuenta_id'];
        $venta['cita_ids'] = [$venta['cita_id'] ?? null];
        $venta['subtotal'] = ($venta['monto_total'] ?? 0) + ($venta['descuento'] ?? 0);
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
        $ventaActual = $this->model->find($id);
        if (!$ventaActual) {
            return $this->redirect('/ventas');
        }
        $cobrosPrevios = $this->cobro->porVenta($id);

        $errors = Validator::validate($data, [
            'descuento' => 'required',
        ]);

        if (empty($citaIds)) {
            $errors['cita_ids'][] = 'Seleccione al menos una cita';
        }

        $cuentaId = isset($data['cuenta_id']) ? (int)$data['cuenta_id'] : null;
        $requiereCuenta = !empty($data['cobrar']) && (int)$data['cobrar'] === 1;

        $descuento = (float)($data['descuento'] ?? 0);
        $serviciosPorCita = $this->cita->serviciosConPrecioPorCita($citaIds);
        $subtotalCalculado = 0;
        foreach ($serviciosPorCita as $detalles) {
            foreach ($detalles as $detalle) {
                $subtotalCalculado += (float)($detalle['precio_base'] ?? 0);
            }
        }

        $subtotalIngresado = isset($data['subtotal']) && $data['subtotal'] !== '' ? (float)$data['subtotal'] : null;
        $subtotal = $subtotalIngresado !== null ? max(0, $subtotalIngresado) : $subtotalCalculado;
        $montoTotal = max(0, $subtotal - $descuento);

        $cuentaObjetivoId = $cuentaId ?: ($cobrosPrevios[0]['cuenta_id'] ?? null);
        $cuentaSeleccionada = $cuentaObjetivoId ? $this->cuenta->findActiva((int)$cuentaObjetivoId) : null;
        $debeReemplazarCobros = $requiereCuenta
            || (!empty($cobrosPrevios) && (
                $montoTotal !== (float)($ventaActual['monto_total'] ?? 0)
                || ($cuentaId && $cuentaId !== (int)($ventaActual['cuenta_id'] ?? 0))
            ));

        if ($debeReemplazarCobros && !$cuentaSeleccionada) {
            $errors['cuenta_id'][] = 'Seleccione una cuenta activa para registrar el cobro.';
        }

        if ($errors) {
            $venta = array_merge($data, ['id' => $id]);
            $venta['cita_ids'] = $citaIds;
            $venta['subtotal'] = $data['subtotal'] ?? $subtotalCalculado;
            $citas = $this->cita->citasConTotales(null, $id);
            $cuentas = $this->cuenta->activos();
            return $this->view('ventas/edit', compact('errors', 'venta', 'citas', 'serviciosPorCita', 'cuentas'));
        }

        $data['cita_id'] = $citaIds[0] ?? null;
        $data['monto_total'] = $montoTotal;
        $montoPagado = ($debeReemplazarCobros && $cuentaSeleccionada) ? $montoTotal : ($ventaActual['monto_pagado'] ?? 0);
        $data['monto_pagado'] = $montoPagado;
        $data['estado_pago'] = $montoPagado >= $montoTotal ? 'pagado' : ($ventaActual['estado_pago'] ?? 'pendiente');
        $data['cuenta_id'] = $debeReemplazarCobros ? ($cuentaSeleccionada['id'] ?? null) : ($ventaActual['cuenta_id'] ?? null);

        if ($debeReemplazarCobros) {
            $this->revertirCobros($id);
        }
        $this->model->update($id, $data);

        if ($debeReemplazarCobros && $cuentaSeleccionada) {
            $this->cobro->registrar($id, (int)$cuentaSeleccionada['id'], $montoTotal);
            $this->cuenta->depositar((int)$cuentaSeleccionada['id'], $montoTotal);
        }
        return $this->redirect('/ventas');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->revertirCobros($id);
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
        $venta = $this->model->find($id);

         if (!$cuenta || $monto <= 0 || !$venta) {
            return $this->redirect('/ventas');
        }

        $this->cuenta->depositar($cuentaId, $monto);
        $this->cobro->registrar($id, $cuentaId, $monto);
        $totalCobrado = $this->cobro->totalPorVenta($id);
        $estadoPago = $totalCobrado >= (float)($venta['monto_total'] ?? 0) ? 'pagado' : 'pendiente';
        $this->model->update($id, [
            'monto_pagado' => $totalCobrado,
            'estado_pago' => $estadoPago,
            'cuenta_id' => $cuentaId,
        ]);
        return $this->redirect('/ventas');
    }

    protected function revertirCobros(int $ventaId): void
    {
        $cobros = $this->cobro->porVenta($ventaId);
        foreach ($cobros as $cobro) {
            if (!empty($cobro['cuenta_id'])) {
                $this->cuenta->retirar((int)$cobro['cuenta_id'], (float)$cobro['monto']);
            }
        }
        if (!empty($cobros)) {
            $this->cobro->eliminarPorVenta($ventaId);
        }
    }
}