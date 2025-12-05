<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Gasto;
use App\Models\Proveedor;
use App\Models\Concepto;
use App\Models\Cuenta;

class GastosController extends Controller
{
    protected Gasto $model;
    protected Proveedor $proveedor;
    protected Concepto $concepto;
    protected Cuenta $cuenta;

    public function __construct()
    {
        $this->model = new Gasto();
        $this->proveedor = new Proveedor();
        $this->concepto = new Concepto();
        $this->cuenta = new Cuenta();
    }

    public function index()
    {
        $fechaIni = Request::get('fecha_ini');
        $fechaFin = Request::get('fecha_fin');
        $proveedorId = Request::get('proveedor_id');
        $proveedorId = $proveedorId !== null && $proveedorId !== '' ? (int)$proveedorId : null;

        if ($fechaIni && $fechaFin && $fechaIni > $fechaFin) {
            [$fechaIni, $fechaFin] = [$fechaFin, $fechaIni];
        }

        $gastos = $this->model->filtrar($fechaIni, $fechaFin, $proveedorId);
        $proveedores = $this->proveedor->activos();
        $proveedorSeleccionado = $proveedorId ? $this->proveedor->find($proveedorId) : null;
        $proveedorLabel = '';

        if ($proveedorSeleccionado) {
            $detalles = array_filter([
                $proveedorSeleccionado['documento'] ?? '',
                $proveedorSeleccionado['telefono'] ?? '',
            ]);
            $proveedorLabel = trim(
                $proveedorSeleccionado['nombre'] . (!empty($detalles) ? ' · ' . implode(' · ', $detalles) : '')
            );
        }

        return $this->view(
            'gastos/index',
            compact('gastos', 'fechaIni', 'fechaFin', 'proveedorId', 'proveedores', 'proveedorLabel')
        );
    }

    public function create()
    {
        $cuentas = $this->cuenta->activos();
        $gasto = ['fecha' => date('Y-m-d')];
        return $this->view('gastos/create', compact('cuentas', 'gasto'));
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'concepto' => 'required|max:150',
            'monto' => 'required',
            'fecha' => 'required',
            'proveedor_id' => 'required',
            'nro_factura' => 'max:50',
            'cuenta_id' => 'max:50',
        ]);

        $cuentaId = isset($data['cuenta_id']) && $data['cuenta_id'] !== '' ? (int)$data['cuenta_id'] : null;
        $conceptoId = isset($data['concepto_id']) && $data['concepto_id'] !== '' ? (int)$data['concepto_id'] : null;
        $cuenta = $cuentaId ? $this->cuenta->findActiva($cuentaId) : null;

        if ($cuentaId && !$cuenta) {
            $errors['cuenta_id'][] = 'Seleccione una cuenta activa.';
        }

        $montoGasto = (float)($data['monto'] ?? 0);
        if ($cuenta && $montoGasto > (float)$cuenta['saldo']) {
            $errors['cuenta_id'][] = 'La cuenta seleccionada no tiene saldo suficiente para cubrir el gasto.';
        }
        
        if ($errors) {
            $gasto = $data;
            if (!empty($gasto['proveedor_id'])) {
                $proveedor = $this->proveedor->find((int)$gasto['proveedor_id']);
                if ($proveedor) {
                    $gasto['proveedor_label'] = trim($proveedor['nombre'] . ' · ' . ($proveedor['documento'] ?? ''));
                }
            }
            if ($conceptoId) {
                $concepto = $this->concepto->find($conceptoId);
                if ($concepto) {
                    $gasto['concepto'] = $concepto['nombre'];
                }
            }
            $cuentas = $this->cuenta->activos();
            return $this->view('gastos/create', compact('errors', 'gasto', 'cuentas'));
        }

        if ($conceptoId && empty($data['concepto'])) {
            $concepto = $this->concepto->find($conceptoId);
            if ($concepto) {
                $data['concepto'] = $concepto['nombre'];
            }
        }

        $data['concepto_id'] = $conceptoId;
        $data['cuenta_id'] = $cuentaId;
        $this->model->create($data);

        if ($cuenta) {
            $this->cuenta->retirar($cuentaId, $montoGasto);
        }
        return $this->redirect('/gastos');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $gasto = $this->model->find($id);
        if (!empty($gasto['proveedor_id'])) {
            $proveedor = $this->proveedor->find((int)$gasto['proveedor_id']);
            if ($proveedor) {
                $gasto['proveedor_nombre'] = trim($proveedor['nombre'] . ' · ' . ($proveedor['documento'] ?? ''));
            }
        }
        if (!empty($gasto['concepto_id'])) {
            $concepto = $this->concepto->find((int)$gasto['concepto_id']);
            if ($concepto) {
                $gasto['concepto'] = $concepto['nombre'];
            }
        }
        $cuentas = $this->cuenta->activos();
        return $this->view('gastos/edit', compact('gasto', 'cuentas'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $gastoActual = $this->model->find($id);
        if (!$gastoActual) {
            return $this->redirect('/gastos');
        }
        $data = Request::all();
        $errors = Validator::validate($data, [
            'concepto' => 'required|max:150',
            'monto' => 'required',
            'fecha' => 'required',
            'proveedor_id' => 'required',
            'nro_factura' => 'max:50',
            'cuenta_id' => 'max:50',
        ]);

        $cuentaId = isset($data['cuenta_id']) && $data['cuenta_id'] !== '' ? (int)$data['cuenta_id'] : null;
        $conceptoId = isset($data['concepto_id']) && $data['concepto_id'] !== '' ? (int)$data['concepto_id'] : null;
        $cuenta = $cuentaId ? $this->cuenta->findActiva($cuentaId) : null;

        if ($cuentaId && !$cuenta) {
            $errors['cuenta_id'][] = 'Seleccione una cuenta activa.';
        }

        $montoNuevo = (float)($data['monto'] ?? 0);
        $montoAnterior = (float)($gastoActual['monto'] ?? 0);
        $cuentaAnteriorId = $gastoActual['cuenta_id'] ?? null;

        if ($cuenta) {
            $saldoDisponible = (float)$cuenta['saldo'];
            if ($cuentaAnteriorId && (int)$cuentaAnteriorId === $cuentaId) {
                $saldoDisponible += $montoAnterior;
            }
            if ($montoNuevo > $saldoDisponible) {
                $errors['cuenta_id'][] = 'La cuenta seleccionada no tiene saldo suficiente para cubrir el gasto.';
            }
        }

        if ($errors) {
            $gasto = array_merge($data, ['id' => $id]);
            if (!empty($gasto['proveedor_id'])) {
                $proveedor = $this->proveedor->find((int)$gasto['proveedor_id']);
                if ($proveedor) {
                    $gasto['proveedor_label'] = trim($proveedor['nombre'] . ' · ' . ($proveedor['documento'] ?? ''));
                }
            }
            if ($conceptoId) {
                $concepto = $this->concepto->find($conceptoId);
                if ($concepto) {
                    $gasto['concepto'] = $concepto['nombre'];
                }
            }
            $cuentas = $this->cuenta->activos();
            return $this->view('gastos/edit', compact('errors', 'gasto', 'cuentas'));
        }

        if ($conceptoId && empty($data['concepto'])) {
            $concepto = $this->concepto->find($conceptoId);
            if ($concepto) {
                $data['concepto'] = $concepto['nombre'];
            }
        }

        $data['concepto_id'] = $conceptoId;
        $data['cuenta_id'] = $cuentaId;
        $this->model->update($id, $data);

        if ($cuentaAnteriorId) {
            $this->cuenta->depositar((int)$cuentaAnteriorId, $montoAnterior);
        }

        if ($cuenta) {
            $this->cuenta->retirar($cuentaId, $montoNuevo);
        }
        return $this->redirect('/gastos');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/gastos');
    }
}