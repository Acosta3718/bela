<?php $title = 'Transferencia entre cuentas'; $errors = $errors ?? []; $transferencia = $transferencia ?? []; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Nueva transferencia</h1>
    <a href="<?= url('/transferencias') ?>" class="btn btn-outline-secondary">Ver transferencias</a>
</div>
<?php include __DIR__ . '/form.php'; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>