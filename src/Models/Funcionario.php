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
        'activo'
    ];
}