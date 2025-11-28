<?php $title = 'Proveedores'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Proveedores</h1>
    <a href="<?= url('/proveedores/crear') ?>" class="btn btn-primary">Nuevo</a>
</div>
<form class="row g-2 mb-3" method="get" action="<?= url('/proveedores') ?>">
    <div class="col-md-4">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre, documento o teléfono" value="<?= htmlspecialchars($busqueda ?? '') ?>">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-secondary">Buscar</button>
        <a class="btn btn-link" href="<?= url('/proveedores') ?>">Limpiar</a>
    </div>
</form>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Teléfono</th>
            <th>Estado</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($proveedores as $proveedor): ?>
        <tr>
            <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
            <td><?= htmlspecialchars($proveedor['documento'] ?? '') ?></td>
            <td><?= htmlspecialchars($proveedor['telefono'] ?? '') ?></td>
            <td><?= htmlspecialchars($proveedor['estado'] ?? '') ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="<?= url('/proveedores/editar?id=' . $proveedor['id']) ?>">Editar</a>
                <form action="<?= url('/proveedores/eliminar?id=' . $proveedor['id']) ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar proveedor?')">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>