<?php $title = 'Editar transferencia'; $errors = $errors ?? []; $transferencia = $transferencia ?? []; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Editar transferencia</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('/transferencias/ver?id=' . $transferencia['id']) ?>" class="btn btn-outline-secondary">Ver</a>
        <a href="<?= url('/transferencias') ?>" class="btn btn-link">Volver al listado</a>
    </div>
</div>
<?php
    $action = url('/transferencias/actualizar?id=' . $transferencia['id']);
    $method = 'put';
    include __DIR__ . '/form.php';
?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>