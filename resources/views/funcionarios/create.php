<?php $title = 'Nuevo funcionario'; $errors = $errors ?? []; $funcionario = $funcionario ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Registrar funcionario</h1>
<form method="post" action="/bela/public/funcionarios">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Guardar</button>
    <a href="/bela/public/funcionarios" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>