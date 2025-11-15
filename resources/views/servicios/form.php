<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($servicio['nombre'] ?? '') ?>">
    <?php if (!empty($errors['nombre'])): ?><div class="text-danger small"><?= implode(', ', $errors['nombre']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Descripción</label>
    <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($servicio['descripcion'] ?? '') ?></textarea>
</div>
<div class="mb-3">
    <label class="form-label">Duración (minutos)</label>
    <input type="number" name="duracion_minutos" class="form-control" value="<?= htmlspecialchars($servicio['duracion_minutos'] ?? '') ?>">
    <?php if (!empty($errors['duracion_minutos'])): ?><div class="text-danger small"><?= implode(', ', $errors['duracion_minutos']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Precio base</label>
    <input type="number" name="precio_base" step="0.01" class="form-control" value="<?= htmlspecialchars($servicio['precio_base'] ?? '') ?>">
    <?php if (!empty($errors['precio_base'])): ?><div class="text-danger small"><?= implode(', ', $errors['precio_base']) ?></div><?php endif; ?>
</div>
<div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" name="activo" <?= !empty($servicio['activo']) ? 'checked' : '' ?>>
    <label class="form-check-label">Activo</label>
</div>