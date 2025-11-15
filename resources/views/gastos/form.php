<div class="mb-3">
    <label class="form-label">Fecha</label>
    <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($gasto['fecha'] ?? '') ?>">
    <?php if (!empty($errors['fecha'])): ?><div class="text-danger small"><?= implode(', ', $errors['fecha']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Concepto</label>
    <input type="text" name="concepto" class="form-control" value="<?= htmlspecialchars($gasto['concepto'] ?? '') ?>">
    <?php if (!empty($errors['concepto'])): ?><div class="text-danger small"><?= implode(', ', $errors['concepto']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Monto</label>
    <input type="number" step="0.01" name="monto" class="form-control" value="<?= htmlspecialchars($gasto['monto'] ?? '') ?>">
    <?php if (!empty($errors['monto'])): ?><div class="text-danger small"><?= implode(', ', $errors['monto']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Notas</label>
    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($gasto['notas'] ?? '') ?></textarea>
</div>