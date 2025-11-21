<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\Servicio;
use DateInterval;
use DateTime;

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
        $serviciosPorCita = $this->model->serviciosPorCita(array_column($citas, 'id'));
        return $this->view('citas/index', compact('citas', 'clientes', 'funcionarios', 'serviciosPorCita'));
    }

    public function create()
    {
        $clientes = $this->cliente->all();
        $funcionarios = $this->funcionariosParaFormulario();
        $servicios = $this->servicio->all();
        $cita = ['fecha' => date('Y-m-d')];
        return $this->view('citas/create', compact('clientes', 'funcionarios', 'servicios', 'cita'));
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
        $serviciosSeleccionados = [];
        if (!empty($data['servicios']) && is_array($data['servicios'])) {
            $serviciosSeleccionados = array_values(array_unique(array_filter(array_map('intval', $data['servicios']))));
        }

        $data['hora_inicio'] = $this->normalizarHora($data['hora_inicio'] ?? null) ?? '';

        if (empty($data['hora_fin']) && !empty($data['hora_inicio']) && !empty($data['fecha']) && !empty($serviciosSeleccionados)) {
            $calculada = $this->calcularHoraFin($data['fecha'], $data['hora_inicio'], $serviciosSeleccionados);
            if ($calculada) {
                $data['hora_fin'] = $calculada;
            }
        }

        $data['hora_fin'] = $this->normalizarHora($data['hora_fin'] ?? null) ?? '';

        $errors = Validator::validate($data, [
            'cliente_id' => 'required',
            'funcionario_id' => 'required',
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required'
        ]);

        if (empty($serviciosSeleccionados)) {
            $errors['servicios'][] = 'Seleccione al menos un servicio';
        }

        if ($this->hayConflictoDeHorario($data)) {
            $errors['hora_inicio'][] = 'El funcionario ya tiene una cita asignada en ese horario.';
        }

        if ($errors) {
            $clientes = $this->cliente->all();
            $funcionarios = $this->funcionariosParaFormulario((int)($data['funcionario_id'] ?? null));
            $servicios = $this->servicio->all();
            $cita = $data;
            $cita['servicios'] = $serviciosSeleccionados;
            return $this->view('citas/create', compact('errors', 'cita', 'clientes', 'funcionarios', 'servicios'));
        }

        $data['estado'] = $data['estado'] ?? 'pendiente';
        $data['servicio_id'] = $serviciosSeleccionados ? (int)$serviciosSeleccionados[0] : null;
        unset($data['servicios']);
        $citaId = $this->model->create($data);
        $this->model->syncServicios($citaId, $serviciosSeleccionados);
        return $this->redirect('/citas');
    }

    public function edit()
    {
        $id = (int)Request::get('id');
        $cita = $this->model->find($id);
        $detalles = $this->model->obtenerServicios($id);
        $cita['servicios'] = array_map(fn($detalle) => (int)$detalle['servicio_id'], $detalles);
        $clientes = $this->cliente->all();
        $funcionarios = $this->funcionariosParaFormulario((int)($cita['funcionario_id'] ?? null));
        $servicios = $this->servicio->all();
        return $this->view('citas/edit', compact('cita', 'clientes', 'funcionarios', 'servicios'));
    }

    public function update()
    {
        $id = (int)Request::get('id');
        $data = Request::all();
        $serviciosSeleccionados = [];
        if (!empty($data['servicios']) && is_array($data['servicios'])) {
            $serviciosSeleccionados = array_values(array_unique(array_filter(array_map('intval', $data['servicios']))));
        }

        $data['hora_inicio'] = $this->normalizarHora($data['hora_inicio'] ?? null) ?? '';

        if (empty($data['hora_fin']) && !empty($data['hora_inicio']) && !empty($data['fecha']) && !empty($serviciosSeleccionados)) {
            $calculada = $this->calcularHoraFin($data['fecha'], $data['hora_inicio'], $serviciosSeleccionados);
            if ($calculada) {
                $data['hora_fin'] = $calculada;
            }
        }

        $data['hora_fin'] = $this->normalizarHora($data['hora_fin'] ?? null) ?? '';

        $errors = Validator::validate($data, [
            'cliente_id' => 'required',
            'funcionario_id' => 'required',
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required'
        ]);

        if (empty($serviciosSeleccionados)) {
            $errors['servicios'][] = 'Seleccione al menos un servicio';
        }

        if ($this->hayConflictoDeHorario($data, $id)) {
            $errors['hora_inicio'][] = 'El funcionario ya tiene una cita asignada en ese horario.';
        }

        if ($errors) {
            $cita = array_merge($data, ['id' => $id]);
            $cita['servicios'] = $serviciosSeleccionados;
            $clientes = $this->cliente->all();
            $funcionarios = $this->funcionariosParaFormulario((int)($data['funcionario_id'] ?? null));
            $servicios = $this->servicio->all();
            return $this->view('citas/edit', compact('errors', 'cita', 'clientes', 'funcionarios', 'servicios'));
        }

        $data['servicio_id'] = $serviciosSeleccionados ? (int)$serviciosSeleccionados[0] : null;
        unset($data['servicios']);
        $this->model->update($id, $data);
        $this->model->syncServicios($id, $serviciosSeleccionados);
        return $this->redirect('/citas');
    }

    private function calcularHoraFin(string $fecha, string $horaInicio, array $servicios): ?string
    {
        $duraciones = $this->servicio->duracionesPorId($servicios);

        if (empty($duraciones)) {
            return null;
        }

        $totalMinutos = array_sum($duraciones);
        if ($totalMinutos <= 0) {
            return null;
        }

        $inicio = new DateTime("{$fecha} {$horaInicio}");
        $fin = (clone $inicio)->add(new DateInterval('PT' . $totalMinutos . 'M'));

        return $fin->format('H:i:s');
    }
    
    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/citas');
    }

    private function funcionariosParaFormulario(?int $actualId = null): array
    {
        $funcionarios = $this->funcionario->activos();

        if ($actualId) {
            $existe = array_filter($funcionarios, fn($item) => (int)$item['id'] === $actualId);
            if (!$existe) {
                $actual = $this->funcionario->find($actualId);
                if ($actual) {
                    $funcionarios[] = $actual;
                }
            }
        }

        usort($funcionarios, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));

        return $funcionarios;
    }

    private function normalizarHora(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);

        if ($valor === '') {
            return null;
        }

        if (preg_match('/^(\d{2}:\d{2})(?::(\d{2}))?$/', $valor, $matches)) {
            $segundos = $matches[2] ?? '00';
            return $matches[1] . ':' . $segundos;
        }

        $fecha = DateTime::createFromFormat('H:i:s', $valor) ?: DateTime::createFromFormat('H:i', $valor);
        if ($fecha) {
            return $fecha->format('H:i:s');
        }

        return null;
    }

    private function hayConflictoDeHorario(array $data, ?int $citaId = null): bool
    {
        if (empty($data['funcionario_id']) || empty($data['fecha']) || empty($data['hora_inicio']) || empty($data['hora_fin'])) {
            return false;
        }

        return $this->model->existeConflicto(
            (int)$data['funcionario_id'],
            $data['fecha'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $citaId
        );
    }
}