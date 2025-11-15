<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Gasto;

class GastosController extends Controller
{
    protected Gasto $model;

    public function __construct()
    {
        $this->model = new Gasto();
    }

    public function index()
    {
        $gastos = $this->model->all();
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
            'fecha' => 'required'
        ]);

        if ($errors) {
            $gasto = $data;
            return $this->view('gastos/create', compact('errors', 'gasto'));
        }

        $this->model->create($data);
        return $this->redirect('/gastos');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $gasto = $this->model->find($id);
        return $this->view('gastos/edit', compact('gasto'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'concepto' => 'required|max:150',
            'monto' => 'required',
            'fecha' => 'required'
        ]);

        if ($errors) {
            $gasto = array_merge($data, ['id' => $id]);
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