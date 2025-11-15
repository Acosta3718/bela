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
        <button class="btn btn-primary">Filtrar</button>
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
            <td>$<?= number_format((float)$fila['ingresos'], 2) ?></td>
            <td>$<?= number_format((float)$fila['cobrado'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th>$<?= number_format($totalIngresos, 2) ?></th>
            <th>$<?= number_format($totalCobrado, 2) ?></th>
        </tr>
    </tfoot>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>