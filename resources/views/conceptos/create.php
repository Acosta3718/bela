<?php $title = 'Nuevo concepto'; $errors = $errors ?? []; $concepto = $concepto ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Nuevo concepto</h1>
<form method="post" action="<?= url('/conceptos') ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Guardar</button>
    <a href="<?= url('/conceptos') ?>" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>