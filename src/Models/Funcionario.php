<?php

namespace App\Models;

use App\Core\Model;

class Funcionario extends Model
{
    protected string $table = 'funcionarios';

    protected array $fillable = [
        'nombre',
        'email',
        'telefono',
        'rol',
        'porcentaje_comision',
        'activo',
        'password'
    ];

    public function activos(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function contarActivos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE activo = 1");
        return (int)$stmt->fetchColumn();
    }
}