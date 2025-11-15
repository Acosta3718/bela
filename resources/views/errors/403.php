<?php $title = 'Acceso denegado'; ob_start(); ?>
<div class="text-center py-5">
    <h1 class="display-4">403</h1>
    <p>No tienes permisos para acceder a este módulo 123.</p>
    <a href="/" class="btn btn-secondary">Regresar</a>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>