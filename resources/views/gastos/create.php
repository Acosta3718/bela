<?php $title = 'Registrar gasto'; $errors = $errors ?? []; $gasto = $gasto ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Registrar gasto</h1>
<form method="post" action="/gastos">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Guardar</button>
    <a href="/gastos" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>