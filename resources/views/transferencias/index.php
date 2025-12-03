<?php $title = 'Transferencias'; $transferencias = $transferencias ?? []; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Transferencias</h1>
    <a href="<?= url('/transferencias/crear') ?>" class="btn btn-primary">Nueva transferencia</a>
</div>
<?php if (empty($transferencias)): ?>
    <div class="alert alert-info">No hay transferencias registradas.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cuenta origen</th>
                    <th>Cuenta destino</th>
                    <th class="text-end">Monto (Gs)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transferencias as $transferencia): ?>
                    <tr>
                        <td><?= htmlspecialchars($transferencia['fecha']) ?></td>
                        <td><?= htmlspecialchars($transferencia['cuenta_origen'] ?? '') ?></td>
                        <td><?= htmlspecialchars($transferencia['cuenta_destino'] ?? '') ?></td>
                        <td class="text-end">Gs <?= number_format((float)$transferencia['monto'], 0, ',', '.') ?></td>
                        <td class="text-end">
                            <a href="<?= url('/transferencias/ver?id=' . $transferencia['id']) ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                            <a href="<?= url('/transferencias/editar?id=' . $transferencia['id']) ?>" class="btn btn-sm btn-primary">Editar</a>
                            <form action="<?= url('/transferencias/eliminar?id=' . $transferencia['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar transferencia?');">
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>