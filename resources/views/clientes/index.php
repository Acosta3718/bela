<?php $title = 'Clientes'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Clientes</h1>
    <a href="/bela/public/clientes/crear" class="btn btn-primary">Nuevo</a>
</div>
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
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>