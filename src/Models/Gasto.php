<?php

namespace App\Models;

use App\Core\Model;

class Gasto extends Model
{
    protected string $table = 'gastos';

    protected array $fillable = [
        'concepto',
        'concepto_id',
        'monto',
        'fecha',
        'notas',
        'proveedor_id',
        'nro_factura',
        'cuenta_id'
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

    public function filtrar(?string $desde, ?string $hasta, ?int $proveedorId = null): array
    {
        $sql =
            'SELECT g.*, p.nombre AS proveedor_nombre, cpt.nombre as concepto_nombre '
            . 'FROM gastos g '
            . 'LEFT JOIN proveedores p ON p.id = g.proveedor_id '
            . 'LEFT JOIN conceptos cpt ON cpt.id = g.concepto_id '
            . 'WHERE 1=1';

        $params = [];

        if (!empty($desde)) {
            $sql .= ' AND g.fecha >= :desde';
            $params['desde'] = $desde;
        }

        if (!empty($hasta)) {
            $sql .= ' AND g.fecha <= :hasta';
            $params['hasta'] = $hasta;
        }

        if ($proveedorId) {
            $sql .= ' AND g.proveedor_id = :proveedor';
            $params['proveedor'] = $proveedorId;
        }

        $sql .= ' ORDER BY g.fecha DESC, g.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}