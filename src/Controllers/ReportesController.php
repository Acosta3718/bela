<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Cuenta;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\Reporte;
use DateInterval;
use DatePeriod;
use DateTime;

class ReportesController extends Controller
{
    protected Reporte $reporte;
    protected Cuenta $cuenta;
    protected Cita $cita;
    protected Funcionario $funcionario;
    protected Cliente $cliente;

    public function __construct()
    {
        $this->reporte = new Reporte();
        $this->cuenta = new Cuenta();
        $this->cita = new Cita();
        $this->funcionario = new Funcionario();
        $this->cliente = new Cliente();
    }

    public function ganancias()
    {
        $inicio = Request::get('inicio', date('Y-m-01'));
        $fin = Request::get('fin', date('Y-m-t'));
        $ganancias = $this->reporte->ganancias($inicio, $fin);
        return $this->view('reportes/ganancias', compact('ganancias', 'inicio', 'fin'));
    }

    public function pagosFuncionarios()
    {
        $inicio = Request::get('inicio', date('Y-m-01'));
        $fin = Request::get('fin', date('Y-m-t'));
        $pagos = $this->reporte->pagosFuncionarios($inicio, $fin);
        return $this->view('reportes/pagos', compact('pagos', 'inicio', 'fin'));
    }

    public function extractoCuentas()
    {
        $inicio = Request::get('inicio', date('Y-m-01'));
        $fin = Request::get('fin', date('Y-m-t'));
        $cuentaId = Request::get('cuenta_id');
        $cuentaId = $cuentaId !== null && $cuentaId !== '' ? (int)$cuentaId : null;
        $resumido = Request::get('resumido') === '1';

        $cuentas = $this->cuenta->activos();
        $extracto = $this->reporte->extractoCuentas($inicio, $fin, $cuentaId);

        return $this->view('reportes/extracto', compact('inicio', 'fin', 'cuentaId', 'resumido', 'extracto', 'cuentas'));
    }

    public function disponibilidadPorDias()
    {
        $inicio = Request::get('inicio', date('Y-m-d'));
        $fin = Request::get('fin', date('Y-m-d'));
        $clienteId = Request::get('cliente_id');
        $clienteId = $clienteId !== null && $clienteId !== '' ? (int)$clienteId : null;

        if ($inicio > $fin) {
            [$inicio, $fin] = [$fin, $inicio];
        }

        $clientes = $this->cliente->all();
        $funcionarios = array_values(array_filter(
            $this->funcionario->activos(),
            static fn(array $funcionario) => (int)($funcionario['disponible_agenda'] ?? 1) === 1
        ));

        $dias = [];
        $periodo = new DatePeriod(new DateTime($inicio), new DateInterval('P1D'), (new DateTime($fin))->modify('+1 day'));

        foreach ($periodo as $dia) {
            $fecha = $dia->format('Y-m-d');
            $citas = $this->reporte->citasPorDia($fecha, $clienteId);
            $disponibilidad = [];

            foreach ($funcionarios as $funcionario) {
                $bloques = $this->cita->bloquesDisponiblesDelDia((int)$funcionario['id'], $fecha);
                $disponibilidad[] = [
                    'funcionario' => $funcionario['nombre'],
                    'bloques' => $bloques,
                ];
            }

            $dias[] = compact('fecha', 'citas', 'disponibilidad');
        }

        return $this->view('reportes/disponibilidad', compact('dias', 'inicio', 'fin', 'clientes', 'clienteId'));
    }
}