<?php

namespace App\Models;

use App\Core\Model;

class Gasto extends Model
{
    protected string $table = 'gastos';

    protected array $fillable = [
        'concepto',
        'monto',
        'fecha',
        'notas'
    ];
}