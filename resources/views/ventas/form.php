<div class="mb-3">
    <label class="form-label">Cita</label>
    <select name="cita_id" class="form-select">
        <option value="">Seleccione</option>
        <?php foreach ($citas as $item): ?>
            <option value="<?= $item['id'] ?>" <?= (($venta['cita_id'] ?? '') == $item['id']) ? 'selected' : '' ?>>Cita #<?= $item['id'] ?> - <?= htmlspecialchars($item['fecha']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($errors['cita_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['cita_id']) ?></div><?php endif; ?>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Monto total</label>
            <input type="number" step="0.01" name="monto_total" class="form-control" value="<?= htmlspecialchars($venta['monto_total'] ?? '') ?>">
            <?php if (!empty($errors['monto_total'])): ?><div class="text-danger small"><?= implode(', ', $errors['monto_total']) ?></div><?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Descuento</label>
            <input type="number" step="0.01" name="descuento" class="form-control" value="<?= htmlspecialchars($venta['descuento'] ?? '0') ?>">
            <?php if (!empty($errors['descuento'])): ?><div class="text-danger small"><?= implode(', ', $errors['descuento']) ?></div><?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Monto pagado</label>
            <input type="number" step="0.01" name="monto_pagado" class="form-control" value="<?= htmlspecialchars($venta['monto_pagado'] ?? '0') ?>">
            <?php if (!empty($errors['monto_pagado'])): ?><div class="text-danger small"><?= implode(', ', $errors['monto_pagado']) ?></div><?php endif; ?>
        </div>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Estado de pago</label>
    <?php $estado = $venta['estado_pago'] ?? 'pendiente'; ?>
    <select name="estado_pago" class="form-select">
        <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
        <option value="parcial" <?= $estado === 'parcial' ? 'selected' : '' ?>>Parcial</option>
        <option value="pagado" <?= $estado === 'pagado' ? 'selected' : '' ?>>Pagado</option>
    </select>
</div>