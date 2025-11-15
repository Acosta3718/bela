<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Servicio;

class ServiciosController extends Controller
{
    protected Servicio $model;

    public function __construct()
    {
        $this->model = new Servicio();
    }

    public function index()
    {
        $servicios = $this->model->all();
        return $this->view('servicios/index', compact('servicios'));
    }

    public function create()
    {
        return $this->view('servicios/create');
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'duracion_minutos' => 'required',
            'precio_base' => 'required'
        ]);

        if ($errors) {
            $servicio = $data;
            return $this->view('servicios/create', compact('errors', 'servicio'));
        }

        $data['activo'] = isset($data['activo']) ? 1 : 0;
        $this->model->create($data);
        return $this->redirect('/servicios');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $servicio = $this->model->find($id);
        return $this->view('servicios/edit', compact('servicio'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'duracion_minutos' => 'required',
            'precio_base' => 'required'
        ]);

        if ($errors) {
            $servicio = array_merge($data, ['id' => $id]);
            return $this->view('servicios/edit', compact('errors', 'servicio'));
        }

        $data['activo'] = isset($data['activo']) ? 1 : 0;
        $this->model->update($id, $data);
        return $this->redirect('/servicios');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/servicios');
    }
}