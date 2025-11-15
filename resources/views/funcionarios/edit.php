<?php $title = 'Editar funcionario'; $errors = $errors ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Editar funcionario</h1>
<form method="post" action="/funcionarios/actualizar?id=<?= $funcionario['id'] ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Actualizar</button>
    <a href="/funcionarios" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>