<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Cuenta extends Model
{
    protected string $table = 'cuentas';

    protected array $fillable = [
        'nombre',
        'saldo',
        'activo',
        'notas',
    ];

    public function activos(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findActiva(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id AND activo = 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function depositar(int $id, float $monto): bool
    {
        if ($monto <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE {$this->table} SET saldo = saldo + :monto WHERE id = :id AND activo = 1");
        return $stmt->execute([
            'monto' => $monto,
            'id' => $id,
        ]);
    }

    public function retirar(int $id, float $monto): bool
    {
        if ($monto <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET saldo = saldo - :monto WHERE id = :id AND activo = 1 AND saldo >= :monto"
        );

        return $stmt->execute([
            'monto' => $monto,
            'id' => $id,
        ]);
    }
}