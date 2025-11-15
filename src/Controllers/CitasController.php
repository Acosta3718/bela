<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\Servicio;

class CitasController extends Controller
{
    protected Cita $model;
    protected Funcionario $funcionario;
    protected Servicio $servicio;
    protected Cliente $cliente;

    public function __construct()
    {
        $this->model = new Cita();
        $this->funcionario = new Funcionario();
        $this->servicio = new Servicio();
        $this->cliente = new Cliente();
    }

    public function index()
    {
        $citas = $this->model->all();
        $clientes = $this->cliente->all();
        $funcionarios = $this->funcionario->all();
        $servicios = $this->servicio->all();
        return $this->view('citas/index', compact('citas', 'clientes', 'funcionarios', 'servicios'));
    }

    public function create()
    {
        $clientes = $this->cliente->all();
        $funcionarios = $this->funcionario->all();
        $servicios = $this->servicio->all();
        return $this->view('citas/create', compact('clientes', 'funcionarios', 'servicios'));
    }

    public function disponibilidad()
    {
        $funcionarioId = (int)Request::get('funcionario_id');
        $fecha = Request::get('fecha');
        $servicioId = (int)Request::get('servicio_id');
        $servicio = $this->servicio->find($servicioId);
        $slots = $this->model->disponibilidad($funcionarioId, $fecha, (int)$servicio['duracion_minutos']);
        return $this->view('citas/disponibilidad', compact('slots'));
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'cliente_id' => 'required',
            'funcionario_id' => 'required',
            'servicio_id' => 'required',
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required'
        ]);

        if ($errors) {
            $clientes = $this->cliente->all();
            $funcionarios = $this->funcionario->all();
            $servicios = $this->servicio->all();
            $cita = $data;
            return $this->view('citas/create', compact('errors', 'cita', 'clientes', 'funcionarios', 'servicios'));
        }

        $data['estado'] = $data['estado'] ?? 'pendiente';
        $this->model->create($data);
        return $this->redirect('/citas');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $cita = $this->model->find($id);
        $clientes = $this->cliente->all();
        $funcionarios = $this->funcionario->all();
        $servicios = $this->servicio->all();
        return $this->view('citas/edit', compact('cita', 'clientes', 'funcionarios', 'servicios'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $errors = Validator::validate($data, [
            'cliente_id' => 'required',
            'funcionario_id' => 'required',
            'servicio_id' => 'required',
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required'
        ]);

        if ($errors) {
            $cita = array_merge($data, ['id' => $id]);
            $clientes = $this->cliente->all();
            $funcionarios = $this->funcionario->all();
            $servicios = $this->servicio->all();
            return $this->view('citas/edit', compact('errors', 'cita', 'clientes', 'funcionarios', 'servicios'));
        }

        $this->model->update($id, $data);
        return $this->redirect('/citas');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/citas');
    }
}