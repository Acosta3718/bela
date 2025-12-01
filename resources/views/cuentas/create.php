<?php $title = 'Nueva cuenta'; $errors = $errors ?? []; $cuenta = $cuenta ?? []; ob_start(); ?>
<h1 class="h3 mb-3">Nueva cuenta</h1>
<form method="post" action="<?= url('/cuentas') ?>">
    <?php include __DIR__ . '/form.php'; ?>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>