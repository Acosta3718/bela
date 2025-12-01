<?php $title = 'Cuentas'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Cuentas</h1>
    <a href="<?= url('/cuentas/crear') ?>" class="btn btn-primary">Nueva cuenta</a>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Saldo</th>
            <th>Estado</th>
            <th>Notas</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cuentas as $cuenta): ?>
        <tr>
            <td><?= htmlspecialchars($cuenta['nombre']) ?></td>
            <td>$<?= number_format((float)($cuenta['saldo'] ?? 0), 2) ?></td>
            <td><?= ($cuenta['activo'] ?? 0) ? 'Activa' : 'Inactiva' ?></td>
            <td><?= htmlspecialchars($cuenta['notas'] ?? '') ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="<?= url('/cuentas/editar?id=' . $cuenta['id']) ?>">Editar</a>
                <form action="<?= url('/cuentas/eliminar?id=' . $cuenta['id']) ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar cuenta?')">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>