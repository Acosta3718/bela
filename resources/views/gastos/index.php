<?php $title = 'Gastos'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Gastos</h1>
    <a href="/bela/public/gastos/crear" class="btn btn-primary">Registrar</a>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Concepto</th>
            <th>Monto</th>
            <th>Notas</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($gastos as $gasto): ?>
        <tr>
            <td><?= htmlspecialchars($gasto['fecha']) ?></td>
            <td><?= htmlspecialchars($gasto['concepto']) ?></td>
            <td>$<?= number_format((float)$gasto['monto'], 2) ?></td>
            <td><?= htmlspecialchars($gasto['notas']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="/bela/public/gastos/editar?id=<?= $gasto['id'] ?>">Editar</a>
                <form action="/bela/public/gastos/eliminar?id=<?= $gasto['id'] ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar gasto?')">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>