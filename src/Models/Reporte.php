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

    public function extractoCuentas(?string $inicio, ?string $fin, ?int $cuentaId = null): array
    {
        $movimientos = $this->movimientosPorCuenta($inicio, $fin, $cuentaId);

        $saldoBase = $this->saldoInicialCuentas($cuentaId);
        $saldoInicial = ($inicio ? $this->saldoHastaFechaAnterior($inicio, $cuentaId) : 0) + $saldoBase;
        $saldo = $saldoInicial;
        $ingresos = 0;
        $egresos = 0;

        foreach ($movimientos as &$movimiento) {
            $monto = (float)$movimiento['monto'];
            if ($movimiento['tipo'] === 'ingreso') {
                $ingresos += $monto;
                $saldo += $monto;
            } else {
                $egresos += $monto;
                $saldo -= $monto;
            }

            $movimiento['saldo'] = $saldo;
        }
        unset($movimiento);

        return [
            'movimientos' => $movimientos,
            'saldo_inicial' => $saldoInicial,
            'saldo_final' => $saldo,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
        ];
    }

    public function citasPorDia(string $fecha, ?int $clienteId = null): array
    {
        $sql = "SELECT c.hora_inicio, c.hora_fin, c.estado, cl.nombre AS cliente_nombre, f.nombre AS funcionario_nombre "
            . "FROM citas c "
            . "JOIN clientes cl ON cl.id = c.cliente_id "
            . "JOIN funcionarios f ON f.id = c.funcionario_id "
            . "WHERE c.fecha = :fecha AND c.estado != 'cancelada'";

        $params = ['fecha' => $fecha];

        if ($clienteId !== null) {
            $sql .= ' AND c.cliente_id = :cliente_id';
            $params['cliente_id'] = $clienteId;
        }

        $sql .= ' ORDER BY c.hora_inicio';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    protected function saldoHastaFechaAnterior(string $inicio, ?int $cuentaId = null): float
    {
        $fechaAnterior = date('Y-m-d', strtotime($inicio . ' -1 day'));

        $movimientosPrevios = $this->movimientosPorCuenta(null, $fechaAnterior, $cuentaId);
        $saldo = 0;

        foreach ($movimientosPrevios as $movimiento) {
            $monto = (float)$movimiento['monto'];
            $saldo += $movimiento['tipo'] === 'ingreso' ? $monto : -$monto;
        }

        return $saldo;
    }

    protected function saldoInicialCuentas(?int $cuentaId = null): float
    {
        $cuentas = $this->obtenerSaldosActuales($cuentaId);

        if (empty($cuentas)) {
            return 0;
        }

        $movimientosHistoricos = $this->movimientosPorCuenta(null, null, $cuentaId);
        $netosPorCuenta = [];

        foreach ($movimientosHistoricos as $movimiento) {
            $monto = (float)$movimiento['monto'];
            $netosPorCuenta[$movimiento['cuenta_id']] = ($netosPorCuenta[$movimiento['cuenta_id']] ?? 0)
                + ($movimiento['tipo'] === 'ingreso' ? $monto : -$monto);
        }

        $saldoInicial = 0;

        foreach ($cuentas as $cuenta) {
            $neto = $netosPorCuenta[$cuenta['id']] ?? 0;
            $saldoInicial += (float)$cuenta['saldo'] - $neto;
        }

        return $saldoInicial;
    }

    protected function obtenerSaldosActuales(?int $cuentaId = null): array
    {
        $sql = 'SELECT id, saldo FROM cuentas WHERE activo = 1';
        $params = [];

        if ($cuentaId !== null) {
            $sql .= ' AND id = :id';
            $params['id'] = $cuentaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
    
    protected function movimientosPorCuenta(?string $inicio, ?string $fin, ?int $cuentaId = null): array
    {
        $params = [];

        $condicionesVentas = ["v.estado_pago = 'pagado'", 'v.cuenta_id IS NOT NULL'];
        $condicionesPagos = ['p.cuenta_id IS NOT NULL'];
        $condicionesTransferenciasIngreso = ['t.cuenta_destino_id IS NOT NULL'];
        $condicionesTransferenciasEgreso = ['t.cuenta_origen_id IS NOT NULL'];
        $condicionesGastos = ['g.cuenta_id IS NOT NULL'];

        if (!empty($inicio)) {
            $condicionesVentas[] = 'c.fecha >= :inicio';
            $condicionesPagos[] = 'p.fecha_pago >= :inicio';
            $condicionesTransferenciasIngreso[] = 't.fecha >= :inicio';
            $condicionesTransferenciasEgreso[] = 't.fecha >= :inicio';
            $condicionesGastos[] = 'g.fecha >= :inicio';
            $params['inicio'] = $inicio;
        }

        if (!empty($fin)) {
            $condicionesVentas[] = 'c.fecha <= :fin';
            $condicionesPagos[] = 'p.fecha_pago <= :fin';
            $condicionesTransferenciasIngreso[] = 't.fecha <= :fin';
            $condicionesTransferenciasEgreso[] = 't.fecha <= :fin';
            $condicionesGastos[] = 'g.fecha <= :fin';
            $params['fin'] = $fin;
        }

        if (!empty($cuentaId)) {
            $condicionesVentas[] = 'v.cuenta_id = :cuenta_id';
            $condicionesPagos[] = 'p.cuenta_id = :cuenta_id';
            $condicionesTransferenciasIngreso[] = 't.cuenta_destino_id = :cuenta_id';
            $condicionesTransferenciasEgreso[] = 't.cuenta_origen_id = :cuenta_id';
            $condicionesGastos[] = 'g.cuenta_id = :cuenta_id';
            $params['cuenta_id'] = $cuentaId;
        }

        $sqlVentas = 'SELECT v.id AS referencia_id, v.cuenta_id, cu.nombre AS cuenta_nombre, c.fecha AS fecha, '
            . 'CONCAT("Cobro venta #", v.id) AS descripcion, '
            . 'COALESCE(NULLIF(v.monto_pagado, 0), v.monto_total) AS monto, '
            . '"ingreso" AS tipo '
            . 'FROM ventas v '
            . 'JOIN citas c ON c.id = v.cita_id '
            . 'JOIN cuentas cu ON cu.id = v.cuenta_id '
            . 'WHERE ' . implode(' AND ', $condicionesVentas);

        $sqlPagos = 'SELECT p.id AS referencia_id, p.cuenta_id, cu.nombre AS cuenta_nombre, p.fecha_pago AS fecha, '
            . 'CONCAT("Pago a ", f.nombre, " (venta #", p.venta_id, ")") AS descripcion, '
            . 'p.monto AS monto, '
            . '"egreso" AS tipo '
            . 'FROM pagos p '
            . 'JOIN funcionarios f ON f.id = p.funcionario_id '
            . 'JOIN cuentas cu ON cu.id = p.cuenta_id '
            . 'WHERE ' . implode(' AND ', $condicionesPagos);

        $sqlTransferIngreso = 'SELECT t.id AS referencia_id, t.cuenta_destino_id AS cuenta_id, cd.nombre AS cuenta_nombre, t.fecha AS fecha, '
        . 'CONCAT("Transferencia desde ", co.nombre) AS descripcion, '
        . 't.monto AS monto, '
        . '"ingreso" AS tipo '
        . 'FROM transferencias t '
        . 'JOIN cuentas cd ON cd.id = t.cuenta_destino_id '
        . 'JOIN cuentas co ON co.id = t.cuenta_origen_id '
        . 'WHERE ' . implode(' AND ', $condicionesTransferenciasIngreso);

        $sqlTransferEgreso = 'SELECT t.id AS referencia_id, t.cuenta_origen_id AS cuenta_id, co.nombre AS cuenta_nombre, t.fecha AS fecha, '
            . 'CONCAT("Transferencia a ", cd.nombre) AS descripcion, '
            . 't.monto AS monto, '
            . '"egreso" AS tipo '
            . 'FROM transferencias t '
            . 'JOIN cuentas co ON co.id = t.cuenta_origen_id '
            . 'JOIN cuentas cd ON cd.id = t.cuenta_destino_id '
            . 'WHERE ' . implode(' AND ', $condicionesTransferenciasEgreso);

        $sqlGastos = 'SELECT g.id AS referencia_id, g.cuenta_id, cu.nombre AS cuenta_nombre, g.fecha AS fecha, '
            . 'CONCAT("Gasto: ", g.concepto) AS descripcion, '
            . 'g.monto AS monto, '
            . '"egreso" AS tipo '
            . 'FROM gastos g '
            . 'JOIN cuentas cu ON cu.id = g.cuenta_id '
            . 'WHERE ' . implode(' AND ', $condicionesGastos);

        $sql = '(' . $sqlVentas . ') UNION ALL (' . $sqlPagos . ') UNION ALL (' . $sqlTransferIngreso . ') UNION ALL (' . $sqlTransferEgreso . ') UNION ALL (' . $sqlGastos . ') '
            . 'ORDER BY fecha ASC, referencia_id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}