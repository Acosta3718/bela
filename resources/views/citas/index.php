<?php $title = 'Citas'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Citas</h1>
    <a href="/citas/crear" class="btn btn-primary">Agendar</a>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Horario</th>
            <th>Cliente</th>
            <th>Funcionario</th>
            <th>Servicio</th>
            <th>Estado</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($citas as $cita): ?>
        <?php
            $cliente = array_values(array_filter($clientes, fn($c) => $c['id'] == $cita['cliente_id']))[0] ?? null;
            $funcionario = array_values(array_filter($funcionarios, fn($f) => $f['id'] == $cita['funcionario_id']))[0] ?? null;
            $servicio = array_values(array_filter($servicios, fn($s) => $s['id'] == $cita['servicio_id']))[0] ?? null;
        ?>
        <tr>
            <td><?= htmlspecialchars($cita['fecha']) ?></td>
            <td><?= htmlspecialchars(substr($cita['hora_inicio'], 0, 5)) ?> - <?= htmlspecialchars(substr($cita['hora_fin'], 0, 5)) ?></td>
            <td><?= htmlspecialchars($cliente['nombre'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($funcionario['nombre'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($servicio['nombre'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($cita['estado']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="/citas/editar?id=<?= $cita['id'] ?>">Editar</a>
                <form action="/citas/eliminar?id=<?= $cita['id'] ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Cancelar cita?')">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>