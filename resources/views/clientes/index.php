<?php $title = 'Clientes'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Clientes</h1>
    <a href="/bela/public/clientes/crear" class="btn btn-primary">Nuevo</a>
</div>
<form method="get" action="<?= url('/clientes') ?>" class="row g-2 align-items-center mb-3">
    <div class="col-md-4">
        <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda ?? '') ?>" class="form-control" placeholder="Buscar por nombre, correo o teléfono">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    </div>
    <?php if (!empty($busqueda)): ?>
    <div class="col-auto">
        <a class="btn btn-link" href="<?= url('/clientes') ?>">Limpiar</a>
    </div>
    <?php endif; ?>
</form>

<?php if (empty($clientes)): ?>
    <p class="text-muted">No se encontraron clientes.</p>
<?php else: ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Notas</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clientes as $cliente): ?>
        <tr>
            <td><?= htmlspecialchars($cliente['nombre']) ?></td>
            <td><?= htmlspecialchars($cliente['email']) ?></td>
            <td><?= htmlspecialchars($cliente['telefono']) ?></td>
            <td><?= htmlspecialchars($cliente['notas']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="/bela/public/clientes/editar?id=<?= $cliente['id'] ?>">Editar</a>
                <form action="/bela/public/clientes/eliminar?id=<?= $cliente['id'] ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar cliente?')">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>