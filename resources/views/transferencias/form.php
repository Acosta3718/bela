<?php
$errors = $errors ?? [];
$transferencia = $transferencia ?? [];
$cuentas = $cuentas ?? [];
$action = $action ?? url('/transferencias');
$method = $method ?? 'post';
$readonly = $readonly ?? false;
?>
<form method="post" action="<?= $action ?>">
    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <label class="form-label">Cuenta origen</label>
            <select name="cuenta_origen_id" class="form-select" <?= $readonly ? 'disabled' : '' ?>>
                <option value="">Seleccione</option>
                <?php foreach ($cuentas as $cuenta): ?>
                    <option value="<?= $cuenta['id'] ?>" <?= ($transferencia['cuenta_origen_id'] ?? '') == $cuenta['id'] ? 'selected' : '' ?>
                        data-saldo="<?= htmlspecialchars($cuenta['saldo'] ?? 0) ?>">
                        <?= htmlspecialchars($cuenta['nombre']) ?> (Gs <?= number_format((float)$cuenta['saldo'], 0, ',', '.') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['cuenta_origen_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['cuenta_origen_id']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-5">
            <label class="form-label">Cuenta destino</label>
            <select name="cuenta_destino_id" class="form-select" <?= $readonly ? 'disabled' : '' ?>>
                <option value="">Seleccione</option>
                <?php foreach ($cuentas as $cuenta): ?>
                    <option value="<?= $cuenta['id'] ?>" <?= ($transferencia['cuenta_destino_id'] ?? '') == $cuenta['id'] ? 'selected' : '' ?>
                        data-saldo="<?= htmlspecialchars($cuenta['saldo'] ?? 0) ?>">
                        <?= htmlspecialchars($cuenta['nombre']) ?> (Gs <?= number_format((float)$cuenta['saldo'], 0, ',', '.') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['cuenta_destino_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['cuenta_destino_id']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-2">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($transferencia['fecha'] ?? date('Y-m-d')) ?>" <?= $readonly ? 'readonly' : '' ?>>
            <?php if (!empty($errors['fecha'])): ?><div class="text-danger small"><?= implode(', ', $errors['fecha']) ?></div><?php endif; ?>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Monto</label>
            <input type="number" step="1" min="1" name="monto" class="form-control" value="<?= htmlspecialchars($transferencia['monto'] ?? '') ?>" <?= $readonly ? 'readonly' : '' ?>>
            <div class="form-text">Ingrese el monto en Guaraníes (Gs) sin decimales.</div>
            <?php if (!empty($errors['monto'])): ?><div class="text-danger small"><?= implode(', ', $errors['monto']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-8">
            <label class="form-label">Notas</label>
            <textarea name="notas" class="form-control" rows="3" placeholder="Detalles de la transferencia (opcional)" <?= $readonly ? 'readonly' : '' ?>><?= htmlspecialchars($transferencia['notas'] ?? '') ?></textarea>
        </div>
    </div>
    <?php if (!$readonly): ?>
        <button class="btn btn-primary">Guardar</button>
        <a href="<?= url('/transferencias') ?>" class="btn btn-link">Cancelar</a>
    <?php else: ?>
        <a href="<?= url('/transferencias/editar?id=' . $transferencia['id']) ?>" class="btn btn-primary">Editar</a>
        <a href="<?= url('/transferencias') ?>" class="btn btn-link">Volver</a>
    <?php endif; ?>
</form>