<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($concepto['nombre'] ?? '') ?>">
    <?php if (!empty($errors['nombre'])): ?><div class="text-danger small"><?= implode(', ', $errors['nombre']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Estado</label>
    <?php $estado = $concepto['estado'] ?? 'activo'; ?>
    <select name="estado" class="form-select">
        <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
        <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
    </select>
    <?php if (!empty($errors['estado'])): ?><div class="text-danger small"><?= implode(', ', $errors['estado']) ?></div><?php endif; ?>
</div>