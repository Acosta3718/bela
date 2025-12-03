<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($cuenta['nombre'] ?? '') ?>">
    <?php if (!empty($errors['nombre'])): ?><div class="text-danger small"><?= implode(', ', $errors['nombre']) ?></div><?php endif; ?>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Saldo</label>
        <input type="number" step="1" name="saldo" class="form-control" value="<?= htmlspecialchars($cuenta['saldo'] ?? '0') ?>">
        <div class="form-text">Saldo inicial en Gs con separador de miles y sin decimales.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Estado</label>
        <select name="activo" class="form-select">
            <option value="1" <?= (($cuenta['activo'] ?? '1') == '1') ? 'selected' : '' ?>>Activa</option>
            <option value="0" <?= (($cuenta['activo'] ?? '') == '0') ? 'selected' : '' ?>>Inactiva</option>
        </select>
        <?php if (!empty($errors['activo'])): ?><div class="text-danger small"><?= implode(', ', $errors['activo']) ?></div><?php endif; ?>
    </div>
</div>
<div class="mb-3 mt-3">
    <label class="form-label">Notas</label>
    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($cuenta['notas'] ?? '') ?></textarea>
</div>
<div class="mt-3">
    <button class="btn btn-primary">Guardar</button>
    <a href="<?= url('/cuentas') ?>" class="btn btn-link">Cancelar</a>
</div>