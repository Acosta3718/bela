<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Funcionario;
use PDOException;

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
            $data['email'] = strtolower(trim($data['email'] ?? ''));
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

            if ($this->model->existeEmail($data['email'])) {
                $funcionario = $data;
                unset($funcionario['password']);
                $errors['email'][] = 'Ya existe un funcionario con este correo electrónico.';
                return $this->view('funcionarios/create', compact('errors', 'funcionario'));
            }

            $data['disponible_agenda'] = isset($data['disponible_agenda']) ? (int)$data['disponible_agenda'] : 1;
            $data['activo'] = isset($data['activo']) ? 1 : 0;
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            try {
                $this->model->create($data);
            } catch (PDOException $e) {
                $funcionario = $data;
                unset($funcionario['password']);
                $errors['email'][] = 'No se pudo guardar el funcionario. Verifique que el correo no exista y vuelva a intentarlo.';
                return $this->view('funcionarios/create', compact('errors', 'funcionario'));
            }
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
            $data['email'] = strtolower(trim($data['email'] ?? ''));
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

            if ($this->model->existeEmail($data['email'], $id)) {
                $funcionario = array_merge($data, ['id' => $id]);
                unset($funcionario['password']);
                $errors['email'][] = 'Ya existe otro funcionario con este correo electrónico.';
                return $this->view('funcionarios/edit', compact('errors', 'funcionario'));
            }

            $data['disponible_agenda'] = isset($data['disponible_agenda']) ? (int)$data['disponible_agenda'] : 1;
            $data['activo'] = isset($data['activo']) ? 1 : 0;
            if ($password !== '') {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }
            try {
                $this->model->update($id, $data);
            } catch (PDOException $e) {
                $funcionario = array_merge($data, ['id' => $id]);
                unset($funcionario['password']);
                $errors['email'][] = 'No se pudo actualizar el funcionario. Verifique que el correo sea único y los datos sean válidos.';
                return $this->view('funcionarios/edit', compact('errors', 'funcionario'));
            }
            return $this->redirect('/funcionarios');
        }

        public function destroy()
        {
            $id = (int)Request::get('id');
            $this->model->delete($id);
            return $this->redirect('/funcionarios');
        }
}