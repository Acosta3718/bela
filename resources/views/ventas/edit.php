<?php $title = 'Editar venta'; $errors = $errors ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Editar venta</h1>
<form method="post" action="/bela/public/ventas/actualizar?id=<?= $venta['id'] ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Actualizar</button>
    <a href="/bela/public/ventas" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>