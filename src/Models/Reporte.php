<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Reporte
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function ganancias(string $inicio, string $fin): array
    {
        $sql = "SELECT DATE(c.fecha) as fecha, SUM(v.monto_total - v.descuento) as ingresos, SUM(v.monto_pagado) as cobrado
                FROM ventas v
                INNER JOIN citas c ON c.id = v.cita_id
                WHERE c.fecha BETWEEN :inicio AND :fin
                GROUP BY DATE(c.fecha)
                ORDER BY fecha";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['inicio' => $inicio, 'fin' => $fin]);
        return $stmt->fetchAll();
    }

    public function pagosFuncionarios(string $inicio, string $fin): array
    {
        $sql = "SELECT f.nombre, SUM((v.monto_total - v.descuento) * (f.porcentaje_comision / 100)) as comision
                FROM citas c
                INNER JOIN funcionarios f ON f.id = c.funcionario_id
                INNER JOIN ventas v ON v.cita_id = c.id
                WHERE c.fecha BETWEEN :inicio AND :fin
                GROUP BY f.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['inicio' => $inicio, 'fin' => $fin]);
        return $stmt->fetchAll();
    }
}