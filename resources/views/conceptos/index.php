<?php $title = 'Conceptos'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Conceptos</h1>
    <a href="<?= url('/conceptos/crear') ?>" class="btn btn-primary">Nuevo</a>
</div>
<form class="row g-3 align-items-end mb-4" method="get" action="<?= url('/conceptos') ?>">
    <div class="col-md-6 col-lg-4">
        <label class="form-label">Buscar</label>
        <input type="text" name="buscar" class="form-control" value="<?= htmlspecialchars($busqueda ?? '') ?>" placeholder="Buscar por nombre">
    </div>
    <div class="col-md-3 col-lg-2">
        <button class="btn btn-secondary w-100">Buscar</button>
    </div>
</form>
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($conceptos)): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">No hay conceptos para mostrar.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($conceptos as $concepto): ?>
                <tr>
                    <td><?= htmlspecialchars($concepto['nombre']) ?></td>
                    <td><?= htmlspecialchars($concepto['estado'] ?? 'activo') ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-secondary" href="<?= url('/conceptos/editar?id=' . $concepto['id']) ?>">Editar</a>
                        <form action="<?= url('/conceptos/eliminar?id=' . $concepto['id']) ?>" method="post" class="d-inline">
                            <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar concepto?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>