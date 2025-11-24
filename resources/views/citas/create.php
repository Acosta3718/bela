<?php
$title = 'Agendar cita';
$errors = $errors ?? [];
$cita = $cita ?? [];
if (empty($cita['fecha'])) {
    $cita['fecha'] = date('Y-m-d');
}
ob_start();
?>
<h1 class="h3 mb-4">Agendar cita</h1>
<form method="post" action="<?= url('/citas') ?>">
    <?php include __DIR__ . '/form.php'; ?>
    <button class="btn btn-primary">Guardar</button>
    <a href="<?= url('/citas') ?>" class="btn btn-link">Cancelar</a>
</form>
<?php include __DIR__ . '/modal_nuevo_cliente.php'; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>