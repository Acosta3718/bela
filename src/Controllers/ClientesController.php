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
        $termino = (string)Request::get('buscar', '');

        if ($termino !== '') {
            $clientes = $this->model->buscar($termino);
        } else {
            $clientes = $this->model->all();
        }

        $busqueda = $termino;

        return $this->view('clientes/index', compact('clientes', 'busqueda'));
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

    public function buscar()
    {
        $termino = (string)Request::get('q', '');
        $clientes = $this->model->buscar($termino, 10);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(array_map(function ($cliente) {
            return [
                'id' => (int)$cliente['id'],
                'nombre' => $cliente['nombre'],
                'email' => $cliente['email'],
                'telefono' => $cliente['telefono'],
            ];
        }, $clientes));
        exit;
    }
}