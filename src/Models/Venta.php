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
        'estado_pago'
    ];

    public function listarConDetalles(?string $desde, ?string $hasta): array
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

        $sql .= ' ORDER BY c.fecha DESC, c.hora_inicio DESC, v.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function pagadasParaPago(int $funcionarioId, ?string $desde = null, ?string $hasta = null, array $ventaIds = []): array
    {
        $params = ['funcionario' => $funcionarioId];
        $sql =
            'SELECT v.id, v.monto_total, v.monto_pagado, v.estado_pago, c.fecha AS cita_fecha, '
            . 'f.porcentaje_comision '
            . 'FROM ventas v '
            . 'JOIN citas c ON c.id = v.cita_id '
            . 'JOIN funcionarios f ON f.id = c.funcionario_id '
            . "WHERE v.estado_pago = 'pagado' AND c.funcionario_id = :funcionario";

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

    public function registrarCobro(int $ventaId, float $monto): bool
    {
        $venta = $this->find($ventaId);
        if (!$venta) {
            return false;
        }
        $nuevoMonto = (float)$venta['monto_pagado'] + $monto;
        $estado = $nuevoMonto >= (float)$venta['monto_total'] ? 'pagado' : 'pendiente';
        return $this->update($ventaId, [
            'monto_pagado' => $nuevoMonto,
            'estado_pago' => $estado
        ]);
    }
}