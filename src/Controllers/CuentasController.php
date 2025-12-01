<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Cuenta;

class CuentasController extends Controller
{
    protected Cuenta $model;

    public function __construct()
    {
        Auth::authorize(['administrador', 'admin']);
        $this->model = new Cuenta();
    }

    public function index()
    {
        $cuentas = $this->model->all();
        return $this->view('cuentas/index', compact('cuentas'));
    }

    public function create()
    {
        return $this->view('cuentas/create');
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'saldo' => 'required',
            'activo' => 'required',
        ]);

        if ($errors) {
            $cuenta = $data;
            return $this->view('cuentas/create', compact('errors', 'cuenta'));
        }

        $this->model->create($data);
        return $this->redirect('/cuentas');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $cuenta = $this->model->find($id);
        return $this->view('cuentas/edit', compact('cuenta'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'saldo' => 'required',
            'activo' => 'required',
        ]);

        if ($errors) {
            $cuenta = array_merge($data, ['id' => $id]);
            return $this->view('cuentas/edit', compact('errors', 'cuenta'));
        }

        $this->model->update($id, $data);
        return $this->redirect('/cuentas');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/cuentas');
    }
}