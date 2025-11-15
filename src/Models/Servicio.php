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
}