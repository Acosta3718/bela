<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Funcionario;

class FuncionariosController extends Controller
{
    protected Funcionario $model;

        public function __construct()
        {
            $this->model = new Funcionario();
        }

        public function index()
        {
            $funcionarios = $this->model->all();
            return $this->view('funcionarios/index', compact('funcionarios'));
        }

        public function create()
        {
            return $this->view('funcionarios/create');
        }

        public function store()
        {
            $data = Request::all();
            $errors = Validator::validate($data, [
                'nombre' => 'required|max:150',
                'email' => 'required|email',
                'telefono' => 'required|max:20',
                'rol' => 'required',
                'porcentaje_comision' => 'required',
                'password' => 'required|min:8'
            ]);

            if ($errors) {
                $funcionario = $data;
                unset($funcionario['password']);
                return $this->view('funcionarios/create', compact('errors', 'funcionario'));
            }

            $data['activo'] = isset($data['activo']) ? 1 : 0;
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $this->model->create($data);
            return $this->redirect('/funcionarios');
        }

        public function edit()
        {
            $id = (int)Request::get('id');
            $funcionario = $this->model->find($id);
            return $this->view('funcionarios/edit', compact('funcionario'));
        }

        public function update()
        {
            $id = (int)Request::get('id');
            $data = Request::all();
            $password = $data['password'] ?? '';
            $rules = [
                'nombre' => 'required|max:150',
                'email' => 'required|email',
                'telefono' => 'required|max:20',
                'rol' => 'required',
                'porcentaje_comision' => 'required'
            ];

            if ($password !== '') {
                $rules['password'] = 'min:8';
            }

            $errors = Validator::validate($data, $rules);

            if ($errors) {
                $funcionario = array_merge($data, ['id' => $id]);
                unset($funcionario['password']);
                return $this->view('funcionarios/edit', compact('errors', 'funcionario'));
            }

            $data['activo'] = isset($data['activo']) ? 1 : 0;
            if ($password !== '') {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }
            $this->model->update($id, $data);
            return $this->redirect('/funcionarios');
        }

        public function destroy()
        {
            $id = (int)Request::get('id');
            $this->model->delete($id);
            return $this->redirect('/funcionarios');
        }
}