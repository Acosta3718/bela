<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Cita;
use App\Models\Funcionario;
use App\Models\Venta;

class VentasController extends Controller
{
    protected Venta $model;
    protected Cita $cita;
    protected Funcionario $funcionario;

    public function __construct()
    {
        $this->model = new Venta();
        $this->cita = new Cita();
        $this->funcionario = new Funcionario();
    }

    public function index()
    {
        $ventas = $this->model->all();
        return $this->view('ventas/index', compact('ventas'));
    }

    public function create()
    {
        $citas = $this->cita->all();
        return $this->view('ventas/create', compact('citas'));
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'cita_id' => 'required',
            'monto_total' => 'required',
            'descuento' => 'required',
            'monto_pagado' => 'required'
        ]);

        if ($errors) {
            $citas = $this->cita->all();
            $venta = $data;
            return $this->view('ventas/create', compact('errors', 'venta', 'citas'));
        }

        $data['estado_pago'] = $data['estado_pago'] ?? 'pendiente';
        $this->model->create($data);
        return $this->redirect('/ventas');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $venta = $this->model->find($id);
        $citas = $this->cita->all();
        return $this->view('ventas/edit', compact('venta', 'citas'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'cita_id' => 'required',
            'monto_total' => 'required',
            'descuento' => 'required',
            'monto_pagado' => 'required'
        ]);

        if ($errors) {
            $venta = array_merge($data, ['id' => $id]);
            $citas = $this->cita->all();
            return $this->view('ventas/edit', compact('errors', 'venta', 'citas'));
        }

        $this->model->update($id, $data);
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
        $this->model->registrarCobro($id, $monto);
        return $this->redirect('/ventas');
    }
}