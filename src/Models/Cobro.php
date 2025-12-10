<?php

namespace App\Models;

use App\Core\Model;

class Cobro extends Model
{
    protected string $table = 'cobros';

    protected array $fillable = [
        'venta_id',
        'cuenta_id',
        'monto',
        'fecha_cobro',
    ];

    public function porVenta(int $ventaId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE venta_id = :venta ORDER BY fecha_cobro DESC, id DESC");
        $stmt->execute(['venta' => $ventaId]);
        return $stmt->fetchAll();
    }

    public function eliminarPorVenta(int $ventaId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE venta_id = :venta");
        return $stmt->execute(['venta' => $ventaId]);
    }

    public function totalPorVenta(int $ventaId): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(monto), 0) AS total FROM {$this->table} WHERE venta_id = :venta");
        $stmt->execute(['venta' => $ventaId]);
        return (float)$stmt->fetchColumn();
    }

    public function totalesPorVenta(array $ventaIds): array
    {
        $ventaIds = array_values(array_filter(array_map('intval', $ventaIds)));
        if (empty($ventaIds)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ventaIds), '?'));
        $sql = "SELECT c1.venta_id, SUM(c1.monto) AS total, "
            . "(SELECT c2.cuenta_id FROM {$this->table} c2 WHERE c2.venta_id = c1.venta_id "
            . "ORDER BY c2.fecha_cobro DESC, c2.id DESC LIMIT 1) AS cuenta_id "
            . "FROM {$this->table} c1 WHERE c1.venta_id IN ({$placeholders}) GROUP BY c1.venta_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($ventaIds);

        $resultados = $stmt->fetchAll();
        $mapa = [];
        foreach ($resultados as $row) {
            $mapa[(int)$row['venta_id']] = [
                'total' => (float)$row['total'],
                'cuenta_id' => isset($row['cuenta_id']) ? (int)$row['cuenta_id'] : null,
            ];
        }

        return $mapa;
    }

    public function registrar(int $ventaId, int $cuentaId, float $monto, ?string $fecha = null): int
    {
        $fechaCobro = $fecha ?: date('Y-m-d');
        return $this->create([
            'venta_id' => $ventaId,
            'cuenta_id' => $cuentaId,
            'monto' => $monto,
            'fecha_cobro' => $fechaCobro,
        ]);
    }
}