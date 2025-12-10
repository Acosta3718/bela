<?php

namespace App\Models;

use App\Core\Model;

class Venta extends Model
{
    protected string $table = 'ventas';

    protected array $fillable = [
        'cita_id',
        'monto_total',
        'descuento',
        'monto_pagado',
        'estado_pago',
        'cuenta_id'
    ];

    public function listarConDetalles(?string $desde, ?string $hasta, ?int $clienteId = null): array
    {
        $sql =
            'SELECT v.*, c.fecha AS cita_fecha, c.hora_inicio, cl.nombre AS cliente '
            . 'FROM ventas v '
            . 'LEFT JOIN citas c ON c.id = v.cita_id '
            . 'LEFT JOIN clientes cl ON cl.id = c.cliente_id '
            . 'WHERE 1=1';

        $params = [];

        if (!empty($desde)) {
            $sql .= ' AND c.fecha >= :desde';
            $params['desde'] = $desde;
        }

        if (!empty($hasta)) {
            $sql .= ' AND c.fecha <= :hasta';
            $params['hasta'] = $hasta;
        }

        if ($clienteId !== null) {
            $sql .= ' AND c.cliente_id = :cliente_id';
            $params['cliente_id'] = $clienteId;
        }
        
        $sql .= ' ORDER BY c.fecha DESC, c.hora_inicio DESC, v.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function pagadasParaPago(int $funcionarioId, ?string $desde = null, ?string $hasta = null, array $ventaIds = []): array
    {
        $params = ['funcionario' => $funcionarioId];
        $sql =
            'SELECT v.id, v.monto_total, COALESCE(cb.total_cobrado, 0) AS monto_pagado, '
            . "CASE WHEN COALESCE(cb.total_cobrado, 0) >= v.monto_total THEN 'pagado' ELSE v.estado_pago END AS estado_pago, "
            . 'c.fecha AS cita_fecha, '
            . 'f.porcentaje_comision '
            . 'FROM ventas v '
            . 'JOIN citas c ON c.id = v.cita_id '
            . 'JOIN funcionarios f ON f.id = c.funcionario_id '
            . 'LEFT JOIN pagos pg ON pg.venta_id = v.id '
            . 'LEFT JOIN (SELECT venta_id, SUM(monto) AS total_cobrado FROM cobros GROUP BY venta_id) cb ON cb.venta_id = v.id '
            . 'WHERE COALESCE(cb.total_cobrado, 0) >= v.monto_total AND c.funcionario_id = :funcionario AND pg.id IS NULL';

        if (!empty($desde)) {
            $sql .= ' AND c.fecha >= :desde';
            $params['desde'] = $desde;
        }

        if (!empty($hasta)) {
            $sql .= ' AND c.fecha <= :hasta';
            $params['hasta'] = $hasta;
        }

        if (!empty($ventaIds)) {
            $placeholders = [];
            foreach (array_values($ventaIds) as $index => $ventaId) {
                $key = 'venta_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = (int)$ventaId;
            }
            $sql .= ' AND v.id IN (' . implode(', ', $placeholders) . ')';
        }

        $sql .= ' ORDER BY c.fecha DESC, v.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function registrarCobro(int $ventaId, float $monto, ?int $cuentaId = null): bool
    {
        $venta = $this->find($ventaId);
        if (!$venta) {
            return false;
        }
        $nuevoMonto = (float)$venta['monto_pagado'] + $monto;
        $estado = $nuevoMonto >= (float)$venta['monto_total'] ? 'pagado' : 'pendiente';
        return $this->update($ventaId, [
            'monto_pagado' => $nuevoMonto,
            'estado_pago' => $estado,
            'cuenta_id' => $cuentaId ?? $venta['cuenta_id'] ?? null,
        ]);
    }
}