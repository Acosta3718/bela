<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Cuenta;
use App\Models\Transferencia;

class TransferenciasController extends Controller
{
    protected Cuenta $cuenta;
    protected Transferencia $transferencia;

    public function __construct()
    {
        $this->cuenta = new Cuenta();
        $this->transferencia = new Transferencia();
    }

    public function index()
    {
        $transferencias = $this->transferencia->todasConCuentas();
        return $this->view('transferencias/index', compact('transferencias'));
    }

    public function create()
    {
        $cuentas = $this->cuenta->activos();
        return $this->view('transferencias/create', compact('cuentas'));
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'cuenta_origen_id' => 'required',
            'cuenta_destino_id' => 'required',
            'monto' => 'required',
            'fecha' => 'required',
        ]);

        $origenId = isset($data['cuenta_origen_id']) ? (int)$data['cuenta_origen_id'] : 0;
        $destinoId = isset($data['cuenta_destino_id']) ? (int)$data['cuenta_destino_id'] : 0;
        $monto = (float)($data['monto'] ?? 0);
        $origen = $this->cuenta->findActiva($origenId);
        $destino = $this->cuenta->findActiva($destinoId);

        if ($origenId === $destinoId) {
            $errors['cuenta_destino_id'][] = 'Seleccione cuentas distintas para transferir fondos.';
        }

        if ($monto <= 0) {
            $errors['monto'][] = 'Ingrese un monto mayor a cero.';
        }

        if (!$origen) {
            $errors['cuenta_origen_id'][] = 'Seleccione una cuenta de origen activa.';
        }

        if (!$destino) {
            $errors['cuenta_destino_id'][] = 'Seleccione una cuenta de destino activa.';
        }

        if ($origen && $monto > (float)$origen['saldo']) {
            $errors['monto'][] = 'El saldo de la cuenta de origen es insuficiente para esta transferencia.';
        }

        if ($errors) {
            $cuentas = $this->cuenta->activos();
            return $this->view('transferencias/create', ['errors' => $errors, 'transferencia' => $data, 'cuentas' => $cuentas]);
        }

        if (!$this->transferencia->tablaDisponible()) {
            $errors['monto'][] = 'La tabla de transferencias no existe. Ejecute el script SQL ubicado en database/transferencias.sql y vuelva a intentarlo.';
            $cuentas = $this->cuenta->activos();
            return $this->view('transferencias/create', ['errors' => $errors, 'transferencia' => $data, 'cuentas' => $cuentas]);
        }

        $registrado = $this->transferencia->registrar(
            $origenId,
            $destinoId,
            $monto,
            $data['fecha'],
            $data['notas'] ?? ''
        );

        if (!$registrado) {
            $errors['monto'][] = 'No se pudo completar la transferencia. Verifique los datos e intente nuevamente.';
            $cuentas = $this->cuenta->activos();
            return $this->view('transferencias/create', ['errors' => $errors, 'transferencia' => $data, 'cuentas' => $cuentas]);
        }

        return $this->redirect('/transferencias');
    }

    public function show()
    {
        $id = (int)Request::get('id');
        $transferencia = $this->transferencia->findConCuentas($id);

        if (!$transferencia) {
            return $this->redirect('/transferencias');
        }

        return $this->view('transferencias/show', compact('transferencia'));
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $transferencia = $this->transferencia->find($id);
        $cuentas = $this->cuenta->activos();

        if (!$transferencia) {
            return $this->redirect('/transferencias');
        }

        return $this->view('transferencias/edit', compact('transferencia', 'cuentas'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'cuenta_origen_id' => 'required',
            'cuenta_destino_id' => 'required',
            'monto' => 'required',
            'fecha' => 'required',
        ]);

        $origenId = isset($data['cuenta_origen_id']) ? (int)$data['cuenta_origen_id'] : 0;
        $destinoId = isset($data['cuenta_destino_id']) ? (int)$data['cuenta_destino_id'] : 0;
        $monto = (float)($data['monto'] ?? 0);
        $origen = $this->cuenta->findActiva($origenId);
        $destino = $this->cuenta->findActiva($destinoId);

        if ($origenId === $destinoId) {
            $errors['cuenta_destino_id'][] = 'Seleccione cuentas distintas para transferir fondos.';
        }

        if ($monto <= 0) {
            $errors['monto'][] = 'Ingrese un monto mayor a cero.';
        }

        if (!$this->transferencia->tablaDisponible()) {
            $errors['monto'][] = 'La tabla de transferencias no existe. Ejecute el script SQL ubicado en database/transferencias.sql y vuelva a intentarlo.';
        }

        if (!$origen) {
            $errors['cuenta_origen_id'][] = 'Seleccione una cuenta de origen activa.';
        }

        if (!$destino) {
            $errors['cuenta_destino_id'][] = 'Seleccione una cuenta de destino activa.';
        }

        if ($origen && $monto > (float)$origen['saldo']) {
            $errors['monto'][] = 'El saldo de la cuenta de origen es insuficiente para esta transferencia.';
        }

        if ($errors) {
            $transferencia = array_merge($data, ['id' => $id]);
            $cuentas = $this->cuenta->activos();
            return $this->view('transferencias/edit', compact('errors', 'transferencia', 'cuentas'));
        }

        $actualizado = $this->transferencia->actualizarTransferencia(
            $id,
            $origenId,
            $destinoId,
            $monto,
            $data['fecha'],
            $data['notas'] ?? ''
        );

        if (!$actualizado) {
            $errors['monto'][] = 'No se pudo actualizar la transferencia. Verifique los datos e intente nuevamente.';
            $transferencia = array_merge($data, ['id' => $id]);
            $cuentas = $this->cuenta->activos();
            return $this->view('transferencias/edit', compact('errors', 'transferencia', 'cuentas'));
        }

        return $this->redirect('/transferencias');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');

        if ($this->transferencia->eliminarTransferencia($id)) {
            return $this->redirect('/transferencias');
        }

        $transferencia = $this->transferencia->find($id);
        if (!$transferencia) {
            return $this->redirect('/transferencias');
        }
        $cuentas = $this->cuenta->activos();
        $errors = ['monto' => ['No se pudo eliminar la transferencia. Verifique saldos y vuelva a intentarlo.']];

        return $this->view('transferencias/edit', compact('errors', 'transferencia', 'cuentas'));
    }
}