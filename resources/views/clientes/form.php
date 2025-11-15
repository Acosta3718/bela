<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>">
    <?php if (!empty($errors['nombre'])): ?><div class="text-danger small"><?= implode(', ', $errors['nombre']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
    <?php if (!empty($errors['email'])): ?><div class="text-danger small"><?= implode(', ', $errors['email']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Teléfono</label>
    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">
    <?php if (!empty($errors['telefono'])): ?><div class="text-danger small"><?= implode(', ', $errors['telefono']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Notas</label>
    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($cliente['notas'] ?? '') ?></textarea>
</div>