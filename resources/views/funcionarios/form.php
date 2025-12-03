<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($funcionario['nombre'] ?? '') ?>">
    <?php if (!empty($errors['nombre'])): ?><div class="text-danger small"><?= implode(', ', $errors['nombre']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($funcionario['email'] ?? '') ?>">
    <?php if (!empty($errors['email'])): ?><div class="text-danger small"><?= implode(', ', $errors['email']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Contraseña<?= !empty($funcionario['id']) ? ' (opcional)' : '' ?></label>
    <input type="password" name="password" class="form-control" autocomplete="new-password">
    <?php if (!empty($errors['password'])): ?><div class="text-danger small"><?= implode(', ', $errors['password']) ?></div><?php endif; ?>
    <?php if (!empty($funcionario['id'])): ?><div class="form-text">Déjelo en blanco para mantener la contraseña actual.</div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Teléfono</label>
    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($funcionario['telefono'] ?? '') ?>">
    <?php if (!empty($errors['telefono'])): ?><div class="text-danger small"><?= implode(', ', $errors['telefono']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Rol</label>
    <?php $rolesDisponibles = ['Administrador', 'Admin', 'Funcionario']; ?>
    <select name="rol" class="form-select">
        <option value="">Seleccione</option>
        <?php foreach ($rolesDisponibles as $rol): ?>
            <option value="<?= htmlspecialchars($rol) ?>" <?= (($funcionario['rol'] ?? '') === $rol) ? 'selected' : '' ?>>
                <?= htmlspecialchars($rol) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($errors['rol'])): ?><div class="text-danger small"><?= implode(', ', $errors['rol']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Porcentaje comisión</label>
    <input type="number" name="porcentaje_comision" class="form-control" value="<?= htmlspecialchars($funcionario['porcentaje_comision'] ?? '') ?>">
    <?php if (!empty($errors['porcentaje_comision'])): ?><div class="text-danger small"><?= implode(', ', $errors['porcentaje_comision']) ?></div><?php endif; ?>
</div>
<div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" name="activo" <?= !empty($funcionario['activo']) ? 'checked' : '' ?>>
    <label class="form-check-label">Activo</label>
</div>