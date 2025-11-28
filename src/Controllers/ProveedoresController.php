<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Proveedor;

class ProveedoresController extends Controller
{
    protected Proveedor $model;

    public function __construct()
    {
        $this->model = new Proveedor();
    }

    public function index()
    {
        $termino = (string)Request::get('buscar', '');
        $proveedores = $termino !== ''
            ? $this->model->buscar($termino)
            : $this->model->all();

        $busqueda = $termino;

        return $this->view('proveedores/index', compact('proveedores', 'busqueda'));
    }

    public function create()
    {
        return $this->view('proveedores/create');
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'documento' => 'max:50',
            'telefono' => 'max:20',
            'direccion' => 'max:150',
            'estado' => 'required',
        ]);

        if ($errors) {
            $proveedor = $data;
            return $this->view('proveedores/create', compact('errors', 'proveedor'));
        }

        $this->model->create($data);
        return $this->redirect('/proveedores');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $proveedor = $this->model->find($id);
        return $this->view('proveedores/edit', compact('proveedor'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'documento' => 'max:50',
            'telefono' => 'max:20',
            'direccion' => 'max:150',
            'estado' => 'required',
        ]);

        if ($errors) {
            $proveedor = array_merge($data, ['id' => $id]);
            return $this->view('proveedores/edit', compact('errors', 'proveedor'));
        }

        $this->model->update($id, $data);
        return $this->redirect('/proveedores');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/proveedores');
    }

    public function buscar()
    {
        $termino = (string)Request::get('q', '');
        $proveedores = $this->model->buscar($termino, 10, true);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(array_map(function ($proveedor) {
            return [
                'id' => (int)$proveedor['id'],
                'nombre' => $proveedor['nombre'],
                'documento' => $proveedor['documento'] ?? '',
                'telefono' => $proveedor['telefono'] ?? '',
            ];
        }, $proveedores));
        exit;
    }
}