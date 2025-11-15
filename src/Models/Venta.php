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