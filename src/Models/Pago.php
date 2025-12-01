<?php

namespace App\Models;

use App\Core\Model;

class Pago extends Model
{
    protected string $table = 'pagos';

    protected array $fillable = [
        'funcionario_id',
        'venta_id',
        'monto',
        'fecha_pago',
        'periodo_inicio',
        'periodo_fin',
        'notas',
        'cuenta_id'
    ];
}