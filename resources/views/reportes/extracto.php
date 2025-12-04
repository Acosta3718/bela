<?php $title = 'Extracto de cuentas'; ob_start(); ?>
<h1 class="h3 mb-4">Extracto de cuentas</h1>
<?php
    $movimientos = $extracto['movimientos'] ?? [];
    $saldoInicial = (float)($extracto['saldo_inicial'] ?? 0);
    $saldoFinal = (float)($extracto['saldo_final'] ?? 0);
    $totalIngresos = (float)($extracto['ingresos'] ?? 0);
    $totalEgresos = (float)($extracto['egresos'] ?? 0);
?>
<form class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="inicio" class="form-control" value="<?= htmlspecialchars($inicio) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Cuenta</label>
        <select name="cuenta_id" class="form-select">
            <option value="">Todas</option>
            <?php foreach ($cuentas as $cuenta): ?>
                <option value="<?= $cuenta['id'] ?>" <?= ($cuentaId ?? '') == $cuenta['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cuenta['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2 d-flex align-items-center">
        <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="resumido" name="resumido" value="1" <?= $resumido ? 'checked' : '' ?>>
            <label class="form-check-label" for="resumido">Ver resumido</label>
        </div>
    </div>
    <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-primary w-100">Filtrar</button>
    </div>
</form>

<div class="mb-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div>
                    <div class="text-muted">Saldo inicial</div>
                    <div class="h5 mb-0">Gs <?= number_format($saldoInicial, 0, ',', '.') ?></div>
                </div>
                <div>
                    <div class="text-muted">Ingresos</div>
                    <div class="h5 mb-0 text-success">Gs <?= number_format($totalIngresos, 0, ',', '.') ?></div>
                </div>
                <div>
                    <div class="text-muted">Egresos</div>
                    <div class="h5 mb-0 text-danger">Gs <?= number_format($totalEgresos, 0, ',', '.') ?></div>
                </div>
                <div>
                    <div class="text-muted">Saldo final</div>
                    <div class="h5 mb-0">Gs <?= number_format($saldoFinal, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($resumido): ?>
    <div class="alert alert-info">Modo resumido activo: se muestran únicamente los totales del rango seleccionado.</div>
<?php endif; ?>

<?php if (!$resumido): ?>
    <?php if (!empty($movimientos)): ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cuenta</th>
                        <th>Detalle</th>
                        <th>Tipo</th>
                        <th class="text-end">Monto</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5"><strong>Saldo inicial antes del rango</strong></td>
                        <td class="text-end">Gs <?= number_format($saldoInicial, 0, ',', '.') ?></td>
                    </tr>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><?= htmlspecialchars($mov['fecha']) ?></td>
                            <td><?= htmlspecialchars($mov['cuenta_nombre']) ?></td>
                            <td><?= htmlspecialchars($mov['descripcion']) ?></td>
                            <td class="text-<?= $mov['tipo'] === 'ingreso' ? 'success' : 'danger' ?>"><?= ucfirst($mov['tipo']) ?></td>
                            <td class="text-end">Gs <?= number_format((float)$mov['monto'], 0, ',', '.') ?></td>
                            <td class="text-end">Gs <?= number_format((float)$mov['saldo'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">Totales en rango</th>
                        <th class="text-end">Gs <?= number_format($totalIngresos - $totalEgresos, 0, ',', '.') ?></th>
                        <th class="text-end">Gs <?= number_format($saldoFinal, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
<?php else: ?>
        <div class="alert alert-light">No se encontraron movimientos para los filtros seleccionados.</div>
    <?php endif; ?>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Detalle</th>
                    <th>Valor neto</th>
                    <th>Saldo final</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Periodo filtrado</td>
                    <td>Gs <?= number_format($totalIngresos - $totalEgresos, 0, ',', '.') ?></td>
                    <td>Gs <?= number_format($saldoFinal, 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>