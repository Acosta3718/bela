<?php $title = 'Panel'; ob_start(); ?>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Citas programadas</h5>
                <p class="display-5 mb-0"><?= $totalCitas ?></p>
                <small class="text-muted">Pendientes y confirmadas</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Funcionarios activos</h5>
                <p class="display-5 mb-0"><?= $totalFuncionarios ?></p>
                <small class="text-muted">Con estado activo</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Ventas registradas</h5>
                <p class="display-5 mb-0"><?= $totalVentas ?></p>
            </div>
        </div>
    </div>
</div>
<?php if ($usuario): ?>
    <div class="alert alert-info mt-4">Bienvenido <?= htmlspecialchars($usuario['name'] ?? 'usuario') ?>.</div>
<?php endif; ?>

<div class="mt-5">
    <h2 class="h4 mb-3">Disponibilidad de hoy (<?= htmlspecialchars($fechaHoy) ?>)</h2>
    <?php if (!empty($disponibilidadHoy)): ?>
        <div class="row g-4">
            <?php foreach ($disponibilidadHoy as $info): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-transparent fw-semibold">
                            <?= htmlspecialchars($info['funcionario']['nombre'] ?? 'Funcionario') ?>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($info['bloques'])): ?>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($info['bloques'] as $bloque): ?>
                                        <li class="py-1">
                                            <span class="badge bg-success">
                                                <?= htmlspecialchars($bloque['inicio']) ?> - <?= htmlspecialchars($bloque['fin']) ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted mb-0">Sin disponibilidad para hoy.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">No hay funcionarios activos registrados.</p>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>