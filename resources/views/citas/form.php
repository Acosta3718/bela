<div class="mb-3">
    <label class="form-label">Cliente</label>
    <select name="cliente_id" class="form-select">
        <option value="">Seleccione</option>
        <?php foreach ($clientes as $item): ?>
            <option value="<?= $item['id'] ?>" <?= (($cita['cliente_id'] ?? '') == $item['id']) ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($errors['cliente_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['cliente_id']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Funcionario</label>
    <select name="funcionario_id" class="form-select">
        <option value="">Seleccione</option>
        <?php foreach ($funcionarios as $item): ?>
            <option value="<?= $item['id'] ?>" <?= (($cita['funcionario_id'] ?? '') == $item['id']) ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($errors['funcionario_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['funcionario_id']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Servicio</label>
    <select name="servicio_id" class="form-select">
        <option value="">Seleccione</option>
        <?php foreach ($servicios as $item): ?>
            <option value="<?= $item['id'] ?>" <?= (($cita['servicio_id'] ?? '') == $item['id']) ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($errors['servicio_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['servicio_id']) ?></div><?php endif; ?>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($cita['fecha'] ?? '') ?>">
            <?php if (!empty($errors['fecha'])): ?><div class="text-danger small"><?= implode(', ', $errors['fecha']) ?></div><?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Hora inicio</label>
            <input type="time" name="hora_inicio" class="form-control" value="<?= htmlspecialchars(substr($cita['hora_inicio'] ?? '', 0, 5)) ?>">
            <?php if (!empty($errors['hora_inicio'])): ?><div class="text-danger small"><?= implode(', ', $errors['hora_inicio']) ?></div><?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Hora fin</label>
            <input type="time" name="hora_fin" class="form-control" value="<?= htmlspecialchars(substr($cita['hora_fin'] ?? '', 0, 5)) ?>">
            <?php if (!empty($errors['hora_fin'])): ?><div class="text-danger small"><?= implode(', ', $errors['hora_fin']) ?></div><?php endif; ?>
        </div>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Estado</label>
    <select name="estado" class="form-select">
        <?php $estado = $cita['estado'] ?? 'pendiente'; ?>
        <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
        <option value="confirmada" <?= $estado === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
        <option value="completada" <?= $estado === 'completada' ? 'selected' : '' ?>>Completada</option>
        <option value="cancelada" <?= $estado === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Notas</label>
    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($cita['notas'] ?? '') ?></textarea>
</div>