<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Cliente;

class ClientesController extends Controller
{
    protected Cliente $model;

    public function __construct()
    {
        $this->model = new Cliente();
    }

    public function index()
    {
        $clientes = $this->model->all();
        return $this->view('clientes/index', compact('clientes'));
    }

    public function create()
    {
        return $this->view('clientes/create');
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'email' => 'email',
            'telefono' => 'required|max:20'
        ]);

        if ($errors) {
            $cliente = $data;
            return $this->view('clientes/create', compact('errors', 'cliente'));
        }

        $this->model->create($data);
        return $this->redirect('/clientes');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $cliente = $this->model->find($id);
        return $this->view('clientes/edit', compact('cliente'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'email' => 'email',
            'telefono' => 'required|max:20'
        ]);

        if ($errors) {
            $cliente = array_merge($data, ['id' => $id]);
            return $this->view('clientes/edit', compact('errors', 'cliente'));
        }

        $this->model->update($id, $data);
        return $this->redirect('/clientes');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/clientes');
    }
}