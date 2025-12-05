<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Concepto;

class ConceptosController extends Controller
{
    protected Concepto $model;

    public function __construct()
    {
        $this->model = new Concepto();
    }

    public function index()
    {
        $termino = (string)Request::get('buscar', '');
        $conceptos = $termino !== ''
            ? $this->model->buscar($termino)
            : $this->model->all();

        $busqueda = $termino;

        return $this->view('conceptos/index', compact('conceptos', 'busqueda'));
    }

    public function create()
    {
        return $this->view('conceptos/create');
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'estado' => 'required'
        ]);

        if ($errors) {
            $concepto = $data;
            return $this->view('conceptos/create', compact('errors', 'concepto'));
        }

        $this->model->create($data);
        return $this->redirect('/conceptos');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $concepto = $this->model->find($id);
        return $this->view('conceptos/edit', compact('concepto'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
            'estado' => 'required'
        ]);

        if ($errors) {
            $concepto = array_merge($data, ['id' => $id]);
            return $this->view('conceptos/edit', compact('errors', 'concepto'));
        }

        $this->model->update($id, $data);
        return $this->redirect('/conceptos');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/conceptos');
    }

    public function buscar()
    {
        $termino = (string)Request::get('q', '');
        $conceptos = $this->model->buscar($termino, 10, true);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(array_map(function ($concepto) {
            return [
                'id' => (int)$concepto['id'],
                'nombre' => $concepto['nombre'],
            ];
        }, $conceptos));
        exit;
    }

    public function storeInline()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'nombre' => 'required|max:150',
        ]);

        header('Content-Type: application/json; charset=utf-8');

        if ($errors) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            exit;
        }

        $data['estado'] = $data['estado'] ?? 'activo';
        $conceptoId = $this->model->create($data);
        $concepto = $this->model->find($conceptoId);

        echo json_encode([
            'id' => (int)$concepto['id'],
            'nombre' => $concepto['nombre'],
            'estado' => $concepto['estado'] ?? 'activo',
        ]);
        exit;
    }
}