<?php $title = 'Editar venta'; $errors = $errors ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Editar venta</h1>
<form method="post" action="<?= url('/ventas/actualizar?id=' . $venta['id']) ?>" id="venta-form">
    <?php include __DIR__ . '/form.php'; ?>
</form>
<?php include __DIR__ . '/ticket_template.php'; ?>
<?php include __DIR__ . '/form_scripts.php'; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>