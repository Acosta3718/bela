<?php $title = 'Detalle de transferencia'; $transferencia = $transferencia ?? []; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Detalle de transferencia</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('/transferencias/editar?id=' . $transferencia['id']) ?>" class="btn btn-primary">Editar</a>
        <a href="<?= url('/transferencias') ?>" class="btn btn-outline-secondary">Volver al listado</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Fecha</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($transferencia['fecha']) ?></dd>

            <dt class="col-sm-3">Cuenta origen</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($transferencia['cuenta_origen'] ?? '') ?></dd>

            <dt class="col-sm-3">Cuenta destino</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($transferencia['cuenta_destino'] ?? '') ?></dd>

            <dt class="col-sm-3">Monto</dt>
            <dd class="col-sm-9">Gs <?= number_format((float)$transferencia['monto'], 0, ',', '.') ?></dd>

            <dt class="col-sm-3">Notas</dt>
            <dd class="col-sm-9"><?= nl2br(htmlspecialchars($transferencia['notas'] ?? '')) ?></dd>
        </dl>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>