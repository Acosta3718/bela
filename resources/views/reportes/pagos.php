<?php $title = 'Pagos por funcionario'; ob_start(); ?>
<h1 class="h3 mb-4">Pagos por funcionario</h1>
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
            <th>Funcionario</th>
            <th>Comisión calculada</th>
        </tr>
    </thead>
    <tbody>
        <?php $total = 0; ?>
        <?php foreach ($pagos as $pago): ?>
        <?php $total += (float)$pago['comision']; ?>
        <tr>
            <td><?= htmlspecialchars($pago['nombre']) ?></td>
            <td>Gs <?= number_format((float)$pago['comision'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <td>Gs <?= number_format((float)$pago['comision'], 0, ',', '.') ?></td>
        </tr>
    </tfoot>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>