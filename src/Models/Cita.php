<?php

namespace App\Models;

use App\Core\Model;
use DateInterval;
use DatePeriod;
use DateTime;
use PDO;

class Cita extends Model
{
    protected string $table = 'citas';

    private ?bool $pivotExiste = null;
    
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

    public function countByStatuses(array $statuses): int
    {
        $statuses = array_values(array_filter(array_map('trim', $statuses)));

        if (empty($statuses)) {
            return 0;
        }

        $placeholders = implode(', ', array_fill(0, count($statuses), '?'));
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE estado IN ({$placeholders})");
        $stmt->execute($statuses);

        return (int)$stmt->fetchColumn();
    }

    public function porFecha(string $fecha, ?int $funcionarioId = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE fecha = :fecha";
        $params = ['fecha' => $fecha];

        if ($funcionarioId !== null) {
            $sql .= " AND funcionario_id = :funcionario";
            $params['funcionario'] = $funcionarioId;
        }

        $sql .= " ORDER BY hora_inicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function bloquesDisponiblesDelDia(int $funcionarioId, string $fecha): array
    {
        $stmt = $this->db->prepare(
            "SELECT hora_inicio, hora_fin FROM {$this->table}"
            . " WHERE funcionario_id = :funcionario AND fecha = :fecha AND estado != 'cancelada'"
            . " ORDER BY hora_inicio"
        );
        $stmt->execute(['funcionario' => $funcionarioId, 'fecha' => $fecha]);
        $citas = $stmt->fetchAll();

        $inicioJornada = new DateTime("{$fecha} 08:00:00");
        $finJornada = new DateTime("{$fecha} 18:00:00");
        $cursor = clone $inicioJornada;
        $bloques = [];

        foreach ($citas as $cita) {
            $inicio = new DateTime("{$fecha} " . $this->obtenerHora($cita['hora_inicio']));
            $fin = new DateTime("{$fecha} " . $this->obtenerHora($cita['hora_fin']));

            if ($inicio > $cursor) {
                $slotFin = $inicio < $finJornada ? $inicio : $finJornada;
                if ($slotFin > $cursor) {
                    $bloques[] = [
                        'inicio' => $cursor->format('H:i'),
                        'fin' => $slotFin->format('H:i')
                    ];
                }
            }

            if ($fin > $cursor) {
                $cursor = $fin;
            }

            if ($cursor >= $finJornada) {
                break;
            }
        }

        if ($cursor < $finJornada) {
            $bloques[] = [
                'inicio' => $cursor->format('H:i'),
                'fin' => $finJornada->format('H:i')
            ];
        }

        return array_values(array_filter($bloques, fn($bloque) => $bloque['inicio'] !== $bloque['fin']));
    }

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
        $stmt = $this->db->prepare(
            "SELECT hora_inicio, hora_fin FROM {$this->table} "
            . "WHERE funcionario_id = :funcionario AND fecha = :fecha AND estado != 'cancelada'"
        );
        $stmt->execute(['funcionario' => $funcionarioId, 'fecha' => $fecha]);
        return $stmt->fetchAll();
    }
    
    protected function obtenerHora(string $valor): string
    {
        $valor = trim($valor);

        if (preg_match('/(\d{2}:\d{2}(?::\d{2})?)/', $valor, $matches)) {
            $time = $matches[1];
            return strlen($time) === 5 ? $time . ':00' : $time;
        }

        $date = new DateTime($valor);
        return $date->format('H:i:s');
    }

    public function syncServicios(int $citaId, array $servicios): void
    {
        if (!$this->pivotExiste()) {
            return;
        }

        $this->db->prepare('DELETE FROM cita_servicios WHERE cita_id = :cita')->execute(['cita' => $citaId]);

        if (empty($servicios)) {
            return;
        }

        $stmt = $this->db->prepare('INSERT INTO cita_servicios (cita_id, servicio_id) VALUES (:cita, :servicio)');

        foreach ($servicios as $servicioId) {
            $stmt->bindValue(':cita', $citaId, PDO::PARAM_INT);
            $stmt->bindValue(':servicio', $servicioId, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    public function obtenerServicios(int $citaId): array
    {
        if (!$this->pivotExiste()) {
            $stmt = $this->db->prepare(
                'SELECT c.id as cita_id, c.servicio_id, s.nombre, s.duracion_minutos '
                . "FROM {$this->table} c "
                . 'JOIN servicios s ON s.id = c.servicio_id '
                . 'WHERE c.id = :cita'
            );
            $stmt->execute(['cita' => $citaId]);
            $row = $stmt->fetch();
            return $row ? [$row] : [];
        }

        $stmt = $this->db->prepare(
            'SELECT cs.cita_id, cs.servicio_id, s.nombre, s.duracion_minutos '
            . 'FROM cita_servicios cs '
            . 'JOIN servicios s ON s.id = cs.servicio_id '
            . 'WHERE cs.cita_id = :cita '
            . 'ORDER BY cs.id'
        );
        $stmt->execute(['cita' => $citaId]);

        return $stmt->fetchAll();
    }

    public function serviciosPorCita(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));

        if ($this->pivotExiste()) {
            $stmt = $this->db->prepare(
                'SELECT cs.cita_id, cs.servicio_id, s.nombre '
                . 'FROM cita_servicios cs '
                . 'JOIN servicios s ON s.id = cs.servicio_id '
                . " WHERE cs.cita_id IN ({$placeholders})"
                . ' ORDER BY cs.cita_id, cs.id'
            );
            $stmt->execute($ids);
            $rows = $stmt->fetchAll();
        } else {
            $stmt = $this->db->prepare(
                'SELECT c.id as cita_id, c.servicio_id, s.nombre '
                . "FROM {$this->table} c "
                . 'JOIN servicios s ON s.id = c.servicio_id '
                . "WHERE c.id IN ({$placeholders})"
                . ' ORDER BY c.id'
            );
            $stmt->execute($ids);
            $rows = $stmt->fetchAll();
        }

        $agrupados = [];
        foreach ($rows as $row) {
            $agrupados[$row['cita_id']][] = $row;
        }

        return $agrupados;
    }

    public function existeConflicto(int $funcionarioId, string $fecha, string $horaInicio, string $horaFin, ?int $excluirId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE funcionario_id = :funcionario AND fecha = :fecha AND estado != 'cancelada'"
            . ' AND hora_inicio < :fin AND hora_fin > :inicio';

        $params = [
            'funcionario' => $funcionarioId,
            'fecha' => $fecha,
            'inicio' => $horaInicio,
            'fin' => $horaFin,
        ];

        if ($excluirId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function pivotExiste(): bool
    {
        if ($this->pivotExiste !== null) {
            return $this->pivotExiste;
        }

        $stmt = $this->db->query("SHOW TABLES LIKE 'cita_servicios'");
        $this->pivotExiste = (bool)$stmt->fetchColumn();

        return $this->pivotExiste;
    }
}