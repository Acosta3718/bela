<?php $title = 'Funcionarios'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Funcionarios</h1>
    <a href="/bela/public/funcionarios/crear" class="btn btn-primary">Nuevo</a>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Rol</th>
            <th>Comisión (%)</th>
            <th>Activo</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($funcionarios as $funcionario): ?>
        <tr>
            <td><?= htmlspecialchars($funcionario['nombre']) ?></td>
            <td><?= htmlspecialchars($funcionario['email']) ?></td>
            <td><?= htmlspecialchars($funcionario['telefono']) ?></td>
            <td><?= htmlspecialchars($funcionario['rol']) ?></td>
            <td><?= htmlspecialchars($funcionario['porcentaje_comision']) ?></td>
            <td><?= $funcionario['activo'] ? 'Sí' : 'No' ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="/bela/public/funcionarios/editar?id=<?= $funcionario['id'] ?>">Editar</a>
                <form action="/bela/public/funcionarios/eliminar?id=<?= $funcionario['id'] ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar funcionario?')">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>