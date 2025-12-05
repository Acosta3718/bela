<?php $title = 'Reporte de ganancias'; ob_start(); ?>
<h1 class="h3 mb-4">Reporte de ganancias</h1>
<form class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label">Inicio</label>
        <input type="date" name="inicio" class="form-control" value="<?= htmlspecialchars($inicio) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Fin</label>
        <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary">Buscar</button>
    </div>
</form>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Ingresos</th>
            <th>Cobrado</th>
        </tr>
    </thead>
    <tbody>
        <?php $totalIngresos = 0; $totalCobrado = 0; ?>
        <?php foreach ($ganancias as $fila): ?>
        <?php $totalIngresos += (float)$fila['ingresos']; $totalCobrado += (float)$fila['cobrado']; ?>
        <tr>
            <td><?= htmlspecialchars($fila['fecha']) ?></td>
            <td>Gs <?= number_format((float)$fila['ingresos'], 0, ',', '.') ?></td>
            <td>Gs <?= number_format((float)$fila['cobrado'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th>Gs <?= number_format($totalIngresos, 0, ',', '.') ?></th>
        <th>Gs <?= number_format($totalCobrado, 0, ',', '.') ?></th>
        </tr>
    </tfoot>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>