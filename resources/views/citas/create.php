<?php $title = 'Agendar cita'; $errors = $errors ?? []; $cita = $cita ?? []; ob_start(); ?>
<h1 class="h3 mb-4">Agendar cita</h1>
<form method="post" action="/citas">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Guardar</button>
    <a href="/citas" class="btn btn-link">Cancelar</a>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>