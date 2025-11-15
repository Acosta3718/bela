<?php $title = 'Registrar pago a funcionario'; $errors = $errors ?? []; $pago = $pago ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Registrar pago a funcionario</h1>
<form method="post" action="/pagos">
    <div class="mb-3">
        <label class="form-label">Funcionario</label>
        <select name="funcionario_id" class="form-select">
            <option value="">Seleccione</option>
            <?php foreach ($funcionarios as $item): ?>
                <option value="<?= $item['id'] ?>" <?= (($pago['funcionario_id'] ?? '') == $item['id']) ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['funcionario_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['funcionario_id']) ?></div><?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Venta</label>
        <select name="venta_id" class="form-select">
            <option value="">Seleccione</option>
            <?php foreach ($ventas as $item): ?>
                <option value="<?= $item['id'] ?>" <?= (($pago['venta_id'] ?? '') == $item['id']) ? 'selected' : '' ?>>Venta #<?= $item['id'] ?> - $<?= number_format((float)$item['monto_total'], 2) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['venta_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['venta_id']) ?></div><?php endif; ?>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Monto</label>
                <input type="number" step="0.01" name="monto" class="form-control" value="<?= htmlspecialchars($pago['monto'] ?? '') ?>">
                <?php if (!empty($errors['monto'])): ?><div class="text-danger small"><?= implode(', ', $errors['monto']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Fecha de pago</label>
                <input type="date" name="fecha_pago" class="form-control" value="<?= htmlspecialchars($pago['fecha_pago'] ?? '') ?>">
                <?php if (!empty($errors['fecha_pago'])): ?><div class="text-danger small"><?= implode(', ', $errors['fecha_pago']) ?></div><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Periodo inicio</label>
                <input type="date" name="periodo_inicio" class="form-control" value="<?= htmlspecialchars($pago['periodo_inicio'] ?? '') ?>">
                <?php if (!empty($errors['periodo_inicio'])): ?><div class="text-danger small"><?= implode(', ', $errors['periodo_inicio']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Periodo fin</label>
                <input type="date" name="periodo_fin" class="form-control" value="<?= htmlspecialchars($pago['periodo_fin'] ?? '') ?>">
                <?php if (!empty($errors['periodo_fin'])): ?><div class="text-danger small"><?= implode(', ', $errors['periodo_fin']) ?></div><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Notas</label>
        <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($pago['notas'] ?? '') ?></textarea>
    </div>
    <button class="btn btn-primary">Guardar</button>
    <a href="/pagos" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>