<?php $errors = $errors ?? []; $proveedor = $proveedor ?? []; ?>
<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($proveedor['nombre'] ?? '') ?>">
    <?php if (!empty($errors['nombre'])): ?><div class="text-danger small"><?= implode(', ', $errors['nombre']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Nro. documento (CI/RUC)</label>
    <input type="text" name="documento" class="form-control" value="<?= htmlspecialchars($proveedor['documento'] ?? '') ?>">
    <?php if (!empty($errors['documento'])): ?><div class="text-danger small"><?= implode(', ', $errors['documento']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Dirección</label>
    <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($proveedor['direccion'] ?? '') ?>">
    <?php if (!empty($errors['direccion'])): ?><div class="text-danger small"><?= implode(', ', $errors['direccion']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Teléfono</label>
    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($proveedor['telefono'] ?? '') ?>">
    <?php if (!empty($errors['telefono'])): ?><div class="text-danger small"><?= implode(', ', $errors['telefono']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Estado</label>
    <select name="estado" class="form-select">
        <?php $estado = $proveedor['estado'] ?? 'activo'; ?>
        <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
        <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
    </select>
    <?php if (!empty($errors['estado'])): ?><div class="text-danger small"><?= implode(', ', $errors['estado']) ?></div><?php endif; ?>
</div>