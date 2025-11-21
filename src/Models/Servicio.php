<?php

namespace App\Models;

use App\Core\Model;

class Servicio extends Model
{
    protected string $table = 'servicios';

    protected array $fillable = [
        'nombre',
        'descripcion',
        'duracion_minutos',
        'precio_base',
        'activo'
    ];

    public function duracionesPorId(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id, duracion_minutos FROM {$this->table} WHERE id IN ({$placeholders})");
        $stmt->execute($ids);

        $duraciones = [];
        foreach ($stmt->fetchAll() as $row) {
            $duraciones[(int)$row['id']] = (int)$row['duracion_minutos'];
        }

        return $duraciones;
    }
}