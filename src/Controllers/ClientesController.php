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
        $data['email'] = $data['email'] === '' ? null : $data['email'];
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'email' => 'nullable|email',
            'telefono' => 'required|max:20'
        ]);

        if ($errors) {
            $cliente = $data;
            return $this->view('clientes/create', compact('errors', 'cliente'));
        }

        $this->model->create($data);
        return $this->redirect('/clientes');
    }

    public function storeInline()
    {
        $data = Request::all();
        $data['email'] = $data['email'] === '' ? null : $data['email'];
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'email' => 'nullable|email',
            'telefono' => 'required|max:20'
        ]);

        header('Content-Type: application/json; charset=utf-8');

        if ($errors) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $clienteId = $this->model->create($data);
        $cliente = $this->model->find($clienteId);

        echo json_encode([
            'id' => (int)$cliente['id'],
            'nombre' => $cliente['nombre'],
            'email' => $cliente['email'],
            'telefono' => $cliente['telefono'],
        ]);
        exit;
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
        $data['email'] = $data['email'] === '' ? null : $data['email'];
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'email' => 'nullable|email',
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