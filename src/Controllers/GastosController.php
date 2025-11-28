<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Gasto;
use App\Models\Proveedor;

class GastosController extends Controller
{
    protected Gasto $model;
    protected Proveedor $proveedor;

    public function __construct()
    {
        $this->model = new Gasto();
        $this->proveedor = new Proveedor();
    }

    public function index()
    {
        $gastos = $this->model->allConProveedor();
        return $this->view('gastos/index', compact('gastos'));
    }

    public function create()
    {
        return $this->view('gastos/create');
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
        ]);

        if ($errors) {
            $gasto = $data;
            if (!empty($gasto['proveedor_id'])) {
                $proveedor = $this->proveedor->find((int)$gasto['proveedor_id']);
                if ($proveedor) {
                    $gasto['proveedor_label'] = trim($proveedor['nombre'] . ' · ' . ($proveedor['documento'] ?? ''));
                }
            }
            return $this->view('gastos/create', compact('errors', 'gasto'));
        }

        $this->model->create($data);
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
        return $this->view('gastos/edit', compact('gasto'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'concepto' => 'required|max:150',
            'monto' => 'required',
            'fecha' => 'required',
            'proveedor_id' => 'required',
            'nro_factura' => 'max:50',
        ]);

        if ($errors) {
            $gasto = array_merge($data, ['id' => $id]);
            if (!empty($gasto['proveedor_id'])) {
                $proveedor = $this->proveedor->find((int)$gasto['proveedor_id']);
                if ($proveedor) {
                    $gasto['proveedor_label'] = trim($proveedor['nombre'] . ' · ' . ($proveedor['documento'] ?? ''));
                }
            }
            return $this->view('gastos/edit', compact('errors', 'gasto'));
        }

        $this->model->update($id, $data);
        return $this->redirect('/gastos');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/gastos');
    }
}