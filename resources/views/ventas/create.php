<?php $title = 'Registrar venta'; $errors = $errors ?? []; $venta = $venta ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Registrar venta</h1>
<form method="post" action="/bela/public/ventas">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Guardar</button>
    <a href="/bela/public/ventas" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>