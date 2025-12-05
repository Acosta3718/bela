<?php $title = 'Editar concepto'; $errors = $errors ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Editar concepto</h1>
<form method="post" action="<?= url('/conceptos/actualizar?id=' . $concepto['id']) ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Guardar</button>
    <a href="<?= url('/conceptos') ?>" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>