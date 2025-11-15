<?php

namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    protected string $table = 'clientes';

    protected array $fillable = [
        'nombre',
        'email',
        'telefono',
        'notas'
    ];
}