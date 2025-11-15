<?php $title = 'Nuevo cliente'; $errors = $errors ?? []; $cliente = $cliente ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Registrar cliente</h1>
<form method="post" action="/clientes">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Guardar</button>
    <a href="/clientes" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>