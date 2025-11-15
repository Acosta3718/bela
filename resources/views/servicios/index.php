<?php $title = 'Servicios'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Servicios</h1>
    <a href="/servicios/crear" class="btn btn-primary">Nuevo</a>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Duración (min)</th>
            <th>Precio base</th>
            <th>Activo</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($servicios as $servicio): ?>
        <tr>
            <td><?= htmlspecialchars($servicio['nombre']) ?></td>
            <td><?= htmlspecialchars($servicio['duracion_minutos']) ?></td>
            <td><?= htmlspecialchars($servicio['precio_base']) ?></td>
            <td><?= $servicio['activo'] ? 'Sí' : 'No' ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="/servicios/editar?id=<?= $servicio['id'] ?>">Editar</a>
                <form action="/servicios/eliminar?id=<?= $servicio['id'] ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar servicio?')">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>