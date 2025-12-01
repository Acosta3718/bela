<?php $title = 'Editar cuenta'; $errors = $errors ?? []; ob_start(); ?>
<h1 class="h3 mb-3">Editar cuenta</h1>
<form method="post" action="<?= url('/cuentas/actualizar?id=' . $cuenta['id']) ?>">
    <?php include __DIR__ . '/form.php'; ?>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>