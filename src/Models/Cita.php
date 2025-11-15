<?php

namespace App\Models;

use App\Core\Model;
use DateInterval;
use DatePeriod;
use DateTime;

class Cita extends Model
{
    protected string $table = 'citas';

    protected array $fillable = [
        'cliente_id',
        'funcionario_id',
        'servicio_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'notas'
    ];

    public function disponibilidad(int $funcionarioId, string $fecha, int $duracionMinutos): array
    {
        $inicioJornada = new DateTime("{$fecha} 08:00:00");
        $finJornada = new DateTime("{$fecha} 18:00:00");
        $interval = new DateInterval('PT15M');
        $period = new DatePeriod($inicioJornada, $interval, $finJornada);

        $ocupados = $this->ocupados($funcionarioId, $fecha);
        $slots = [];

        foreach ($period as $start) {
            $end = (clone $start)->add(new DateInterval('PT' . $duracionMinutos . 'M'));
            if ($end > $finJornada) {
                break;
            }
            $slotKey = $start->format('H:i') . '-' . $end->format('H:i');
            $disponible = true;
            foreach ($ocupados as $cita) {
                $citaInicio = new DateTime($cita['hora_inicio']);
                $citaFin = new DateTime($cita['hora_fin']);
                if ($start < $citaFin && $end > $citaInicio) {
                    $disponible = false;
                    break;
                }
            }
            $slots[] = [
                'label' => $slotKey,
                'inicio' => $start->format('H:i:s'),
                'fin' => $end->format('H:i:s'),
                'disponible' => $disponible
            ];
        }

        return $slots;
    }

    public function ocupados(int $funcionarioId, string $fecha): array
    {
        $stmt = $this->db->prepare("SELECT hora_inicio, hora_fin FROM {$this->table} WHERE funcionario_id = :funcionario AND fecha = :fecha AND estado != 'cancelada'");
        $stmt->execute(['funcionario' => $funcionarioId, 'fecha' => $fecha]);
        return $stmt->fetchAll();
    }
}