<?php $title = 'Editar servicio'; $errors = $errors ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Editar servicio</h1>
<form method="post" action="/bela/public/servicios/actualizar?id=<?= $servicio['id'] ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Actualizar</button>
    <a href="/bela/public/servicios" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>