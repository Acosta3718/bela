<?php $title = 'Panel'; ob_start(); ?>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Citas programadas</h5>
                <p class="display-5 mb-0"><?= $totalCitas ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Funcionarios activos</h5>
                <p class="display-5 mb-0"><?= $totalFuncionarios ?></p>
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
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>