<?php $title = 'No encontrado'; ob_start(); ?>
<div class="text-center py-5">
    <h1 class="display-4">404</h1>
    <p>La página <?= htmlspecialchars($uri) ?> no existe.</p>
    <a href="/" class="btn btn-primary">Volver al inicio</a>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>