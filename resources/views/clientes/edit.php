<?php $title = 'Editar cliente'; $errors = $errors ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Editar cliente</h1>
<form method="post" action="/bela/public/clientes/actualizar?id=<?= $cliente['id'] ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Actualizar</button>
    <a href="/bela/public/clientes" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>