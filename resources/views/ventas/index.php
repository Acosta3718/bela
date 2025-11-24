<?php $title = 'Ventas'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Ventas</h1>
    <a href="<?= url('/ventas/crear') ?>" class="btn btn-primary">Nueva</a>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Cita</th>
            <th>Monto total</th>
            <th>Descuento</th>
            <th>Pagado</th>
            <th>Estado</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ventas as $venta): ?>
        <tr>
            <td>#<?= htmlspecialchars($venta['cita_id']) ?></td>
            <td>$<?= number_format((float)$venta['monto_total'], 2) ?></td>
            <td>$<?= number_format((float)$venta['descuento'], 2) ?></td>
            <td>$<?= number_format((float)$venta['monto_pagado'], 2) ?></td>
            <td><?= htmlspecialchars($venta['estado_pago']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="<?= url('/ventas/editar?id=' . $venta['id']) ?>">Editar</a>
                <form action="<?= url('/ventas/eliminar?id=' . $venta['id']) ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar venta?')">Eliminar</button>
                </form>
                <form action="<?= url('/ventas/cobro?id=' . $venta['id']) ?>" method="post" class="d-inline">
                    <input type="hidden" name="monto" value="<?= max(0, $venta['monto_total'] - $venta['monto_pagado']) ?>">
                    <button class="btn btn-sm btn-success">Marcar pagado</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>