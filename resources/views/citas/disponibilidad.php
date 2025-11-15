<?php $title = 'Disponibilidad'; ob_start(); ?>
<h2 class="h4 mb-3">Disponibilidad</h2>
<div class="row g-3">
    <?php foreach ($slots as $slot): ?>
    <div class="col-md-3">
        <div class="card border-<?= $slot['disponible'] ? 'success' : 'danger' ?>">
            <div class="card-body text-center">
                <p class="fw-bold mb-1"><?= htmlspecialchars($slot['label']) ?></p>
                <span class="badge bg-<?= $slot['disponible'] ? 'success' : 'danger' ?>">
                    <?= $slot['disponible'] ? 'Disponible' : 'Ocupado' ?>
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>