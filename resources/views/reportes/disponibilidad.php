<?php $title = 'Disponibilidad por días'; ob_start(); ?>
<h1 class="h3 mb-4">Consulta de disponibilidad por días</h1>

<form class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="inicio" class="form-control" value="<?= htmlspecialchars($inicio) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Cliente</label>
        <select name="cliente_id" class="form-select">
            <option value="">Todos</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id'] ?>" <?= ($clienteId ?? '') == $cliente['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cliente['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Consultar</button>
    </div>
</form>

<?php if (!empty($dias)): ?>
    <?php foreach ($dias as $dia): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?= htmlspecialchars(date('d/m/Y', strtotime($dia['fecha']))) ?></strong>
                <span class="text-muted">Disponibilidad y citas registradas</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h5 class="mb-3">Citas registradas</h5>
                        <?php if (!empty($dia['citas'])): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Horario</th>
                                            <th>Cliente</th>
                                            <th>Funcionario</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dia['citas'] as $cita): ?>
                                            <tr>
                                                <td><?= htmlspecialchars(substr($cita['hora_inicio'], 0, 5)) ?> - <?= htmlspecialchars(substr($cita['hora_fin'], 0, 5)) ?></td>
                                                <td><?= htmlspecialchars($cita['cliente_nombre']) ?></td>
                                                <td><?= htmlspecialchars($cita['funcionario_nombre']) ?></td>
                                                <td><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($cita['estado']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light mb-0">No hay citas registradas para este día con los filtros seleccionados.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3">Horarios disponibles</h5>
                        <?php $hayDisponibilidad = false; ?>
                        <?php foreach ($dia['disponibilidad'] as $disponible): ?>
                            <div class="mb-3">
                                <div class="fw-semibold mb-1"><?= htmlspecialchars($disponible['funcionario']) ?></div>
                                <?php if (!empty($disponible['bloques'])): ?>
                                    <?php $hayDisponibilidad = true; ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($disponible['bloques'] as $bloque): ?>
                                            <span class="badge bg-success"><?= htmlspecialchars($bloque['inicio']) ?> - <?= htmlspecialchars($bloque['fin']) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted">Sin espacios disponibles.</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$hayDisponibilidad): ?>
                            <div class="alert alert-warning mb-0">No se encontraron horarios disponibles para este día.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-light">Seleccione un rango de fechas para consultar la disponibilidad.</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>