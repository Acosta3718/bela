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
        'notas',
        'proveedor_id',
        'nro_factura'
    ];

    public function allConProveedor(): array
    {
        $stmt = $this->db->query(
            'SELECT g.*, p.nombre AS proveedor_nombre '
            . 'FROM gastos g '
            . 'LEFT JOIN proveedores p ON p.id = g.proveedor_id '
            . 'ORDER BY g.id DESC'
        );

        return $stmt->fetchAll();
    }
}