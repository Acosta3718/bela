<?php

namespace App\Models;

use App\Core\Model;
use PDOException;

class Transferencia extends Model
{
    protected string $table = 'transferencias';

    private ?bool $tablaExiste = null;

    protected array $fillable = [
        'cuenta_origen_id',
        'cuenta_destino_id',
        'monto',
        'fecha',
        'notas',
    ];

    public function registrar(int $cuentaOrigenId, int $cuentaDestinoId, float $monto, string $fecha, string $notas = ''): bool
    {
        if ($monto <= 0 || $cuentaOrigenId === $cuentaDestinoId || !$this->asegurarTabla()) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $origen = $this->bloquearCuenta($cuentaOrigenId);
            $destino = $this->bloquearCuenta($cuentaDestinoId);

            if (!$origen || !$destino) {
                $this->db->rollBack();
                return false;
            }

            if ((float)$origen['saldo'] < $monto) {
                $this->db->rollBack();
                return false;
            }

            $this->ajustarSaldo($cuentaOrigenId, -$monto);
            $this->ajustarSaldo($cuentaDestinoId, $monto);

            $this->create([
                'cuenta_origen_id' => $cuentaOrigenId,
                'cuenta_destino_id' => $cuentaDestinoId,
                'monto' => $monto,
                'fecha' => $fecha,
                'notas' => $notas,
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function actualizarTransferencia(int $id, int $cuentaOrigenId, int $cuentaDestinoId, float $monto, string $fecha, string $notas = ''): bool
    {
        if ($monto <= 0 || $cuentaOrigenId === $cuentaDestinoId || !$this->asegurarTabla()) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $registroStmt = $this->db->prepare('SELECT * FROM transferencias WHERE id = :id FOR UPDATE');
            $registroStmt->execute(['id' => $id]);
            $actual = $registroStmt->fetch();

            if (!$actual) {
                $this->db->rollBack();
                return false;
            }

            $origenActual = (int)$actual['cuenta_origen_id'];
            $destinoActual = (int)$actual['cuenta_destino_id'];

            $idsPorBloquear = array_unique([$origenActual, $destinoActual, $cuentaOrigenId, $cuentaDestinoId]);
            $cuentas = [];
            foreach ($idsPorBloquear as $cuentaId) {
                $cuenta = $this->bloquearCuenta($cuentaId, false);
                if (!$cuenta) {
                    $this->db->rollBack();
                    return false;
                }
                $cuentas[$cuentaId] = $cuenta;
            }

            if ((float)$cuentas[$destinoActual]['saldo'] < (float)$actual['monto']) {
                $this->db->rollBack();
                return false;
            }

            $this->ajustarSaldo($origenActual, (float)$actual['monto']);
            $this->ajustarSaldo($destinoActual, -(float)$actual['monto']);

            $saldoOrigenNuevo = $this->obtenerSaldo($cuentaOrigenId);
            if ($saldoOrigenNuevo === null || $saldoOrigenNuevo < $monto) {
                $this->db->rollBack();
                return false;
            }

            $this->ajustarSaldo($cuentaOrigenId, -$monto);
            $this->ajustarSaldo($cuentaDestinoId, $monto);

            $this->update($id, [
                'cuenta_origen_id' => $cuentaOrigenId,
                'cuenta_destino_id' => $cuentaDestinoId,
                'monto' => $monto,
                'fecha' => $fecha,
                'notas' => $notas,
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function eliminarTransferencia(int $id): bool
    {
        if (!$this->asegurarTabla()) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $registroStmt = $this->db->prepare('SELECT * FROM transferencias WHERE id = :id FOR UPDATE');
            $registroStmt->execute(['id' => $id]);
            $actual = $registroStmt->fetch();

            if (!$actual) {
                $this->db->rollBack();
                return false;
            }

            $origen = $this->bloquearCuenta((int)$actual['cuenta_origen_id'], false);
            $destino = $this->bloquearCuenta((int)$actual['cuenta_destino_id'], false);

            if (!$origen || !$destino || (float)$destino['saldo'] < (float)$actual['monto']) {
                $this->db->rollBack();
                return false;
            }

            $this->ajustarSaldo((int)$actual['cuenta_origen_id'], (float)$actual['monto']);
            $this->ajustarSaldo((int)$actual['cuenta_destino_id'], -(float)$actual['monto']);
            $this->delete($id);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function todasConCuentas(): array
    {
        if (!$this->asegurarTabla()) {
            return [];
        }

        $sql = 'SELECT t.*, co.nombre AS cuenta_origen, cd.nombre AS cuenta_destino '
            . 'FROM transferencias t '
            . 'JOIN cuentas co ON co.id = t.cuenta_origen_id '
            . 'JOIN cuentas cd ON cd.id = t.cuenta_destino_id '
            . 'ORDER BY t.fecha DESC, t.id DESC';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function findConCuentas(int $id): ?array
    {
        if (!$this->asegurarTabla()) {
            return null;
        }

        $sql = 'SELECT t.*, co.nombre AS cuenta_origen, cd.nombre AS cuenta_destino '
            . 'FROM transferencias t '
            . 'JOIN cuentas co ON co.id = t.cuenta_origen_id '
            . 'JOIN cuentas cd ON cd.id = t.cuenta_destino_id '
            . 'WHERE t.id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    private function asegurarTabla(): bool
    {
        if ($this->tablaExiste !== null) {
            return $this->tablaExiste;
        }

        try {
            $stmt = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
            $this->tablaExiste = (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->tablaExiste = false;
        }

        return $this->tablaExiste;
    }

    public function tablaDisponible(): bool
    {
        return $this->asegurarTabla();
    }

    protected function bloquearCuenta(int $id, bool $soloActiva = true): ?array
    {
        $sql = 'SELECT * FROM cuentas WHERE id = :id' . ($soloActiva ? ' AND activo = 1' : '') . ' FOR UPDATE';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function obtenerSaldo(int $cuentaId): ?float
    {
        $stmt = $this->db->prepare('SELECT saldo FROM cuentas WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $cuentaId]);
        $row = $stmt->fetch();
        return $row ? (float)$row['saldo'] : null;
    }

    private function ajustarSaldo(int $cuentaId, float $diferencia): void
    {
        $stmt = $this->db->prepare('UPDATE cuentas SET saldo = saldo + :monto WHERE id = :id');
        $stmt->execute(['monto' => $diferencia, 'id' => $cuentaId]);
    }
}