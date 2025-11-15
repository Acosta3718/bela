<?php $title = 'Pagos a funcionarios'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Pagos a funcionarios</h1>
    <a href="/bela/public/pagos/crear" class="btn btn-primary">Registrar pago</a>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Funcionario</th>
            <th>Monto</th>
            <th>Fecha pago</th>
            <th>Periodo</th>
            <th>Notas</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pagos as $pago): ?>
        <?php $funcionario = array_values(array_filter($funcionarios, fn($f) => $f['id'] == $pago['funcionario_id']))[0] ?? null; ?>
        <tr>
            <td><?= htmlspecialchars($funcionario['nombre'] ?? 'N/A') ?></td>
            <td>$<?= number_format((float)$pago['monto'], 2) ?></td>
            <td><?= htmlspecialchars($pago['fecha_pago']) ?></td>
            <td><?= htmlspecialchars($pago['periodo_inicio']) ?> - <?= htmlspecialchars($pago['periodo_fin']) ?></td>
            <td><?= htmlspecialchars($pago['notas']) ?></td>
            <td class="text-end">
                <form action="/bela/public/pagos/eliminar?id=<?= $pago['id'] ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar pago?')">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>