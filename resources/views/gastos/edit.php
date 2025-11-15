<?php $title = 'Editar gasto'; $errors = $errors ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Editar gasto</h1>
<form method="post" action="/gastos/actualizar?id=<?= $gasto['id'] ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Actualizar</button>
    <a href="/gastos" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>