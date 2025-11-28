<?php $title = 'Ventas'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Ventas</h1>
    <a href="<?= url('/ventas/crear') ?>" class="btn btn-primary">Nueva</a>
</div>
<form class="row g-3 align-items-end mb-4" method="get" action="<?= url('/ventas') ?>">
    <div class="col-md-3">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="fecha_ini" class="form-control" value="<?= htmlspecialchars($fechaIni ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin ?? '') ?>">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-secondary">Filtrar</button>
        <a class="btn btn-link" href="<?= url('/ventas') ?>">Limpiar</a>
    </div>
</form>
<?php include __DIR__ . '/ticket_template.php'; ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Cita</th>
            <th>Cliente</th>
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
            <td><?= htmlspecialchars($venta['cliente'] ?? 'N/A') ?></td>
            <td>Gs <?= number_format((float)$venta['monto_total'], 2) ?></td>
            <td>Gs <?= number_format((float)$venta['descuento'], 2) ?></td>
            <td>Gs <?= number_format((float)$venta['monto_pagado'], 2) ?></td>
            <td><?= htmlspecialchars($venta['estado_pago']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="<?= url('/ventas/editar?id=' . $venta['id']) ?>">Editar</a>
                <form action="<?= url('/ventas/eliminar?id=' . $venta['id']) ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar venta?')">Eliminar</button>
                </form>
                <form
                    action="<?= url('/ventas/cobro?id=' . $venta['id']) ?>"
                    method="post"
                    class="d-inline form-cobro"
                    data-venta-id="<?= $venta['id'] ?>"
                    data-cita-id="<?= $venta['cita_id'] ?>"
                >
                    <input type="hidden" name="monto" value="<?= max(0, $venta['monto_total'] - $venta['monto_pagado']) ?>">
                    <button class="btn btn-sm btn-success">Marcar pagado</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<script>
    const ventasData = <?= json_encode($ventas ?? []) ?>;
    const citaServicios = <?= json_encode($serviciosPorCita ?? []) ?>;
    const citasInfo = <?= json_encode($citasInfo ?? []) ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const ventasMap = ventasData.reduce((acc, venta) => {
            acc[venta.id] = venta;
            return acc;
        }, {});

        document.querySelectorAll('.form-cobro').forEach(form => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.enviando === '1') return;
                event.preventDefault();

                const ventaId = form.dataset.ventaId;
                const venta = ventasMap[ventaId] || {};
                const cita = venta.cita_id ? (citasInfo[venta.cita_id] || {}) : {};
                const servicios = venta.cita_id ? (citaServicios[venta.cita_id] || []) : [];
                const ticketData = {
                    cliente: venta.cliente ?? cita.cliente ?? 'N/A',
                    citas: venta.cita_id ? [{ id: venta.cita_id, fecha: cita.fecha, hora: cita.hora_inicio }] : [],
                    servicios,
                    descuento: venta.descuento || 0,
                };

                if (window.ticketPrinter) {
                    window.ticketPrinter.printTicket(ticketData);
                }

                form.dataset.enviando = '1';
                form.submit();
            });
        });
    });
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>