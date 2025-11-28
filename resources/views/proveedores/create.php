<?php $title = 'Registrar proveedor'; $errors = $errors ?? []; $proveedor = $proveedor ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Registrar proveedor</h1>
<form method="post" action="<?= url('/proveedores') ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Guardar</button>
    <a href="<?= url('/proveedores') ?>" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>