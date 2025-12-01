<?php $title = 'Gastos'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Gastos</h1>
    <a href="/bela/public/gastos/crear" class="btn btn-primary">Registrar</a>
</div>
<form class="row g-3 align-items-end mb-4" method="get" action="<?= url('/gastos') ?>">
    <div class="col-md-3">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="fecha_ini" class="form-control" value="<?= htmlspecialchars($fechaIni ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Proveedor</label>
        <select name="proveedor_id" class="form-select">
            <option value="">Todos</option>
            <?php foreach ($proveedores ?? [] as $proveedor): ?>
                <option value="<?= $proveedor['id'] ?>" <?= ($proveedorId ?? '') == $proveedor['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($proveedor['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-secondary">Filtrar</button>
        <a class="btn btn-link" href="<?= url('/gastos') ?>">Limpiar</a>
    </div>
</form>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Concepto</th>
            <th>Proveedor</th>
            <th>Nro. factura</th>
            <th>Monto</th>
            <th>Notas</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($gastos)): ?>
            <tr>
                <td colspan="7" class="text-center text-muted">No hay gastos para los filtros seleccionados.</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($gastos as $gasto): ?>
        <tr>
            <td><?= htmlspecialchars($gasto['fecha']) ?></td>
            <td><?= htmlspecialchars($gasto['concepto']) ?></td>
            <td><?= htmlspecialchars($gasto['proveedor_nombre'] ?? '') ?></td>
            <td><?= htmlspecialchars($gasto['nro_factura'] ?? '') ?></td>
            <td>$<?= number_format((float)$gasto['monto'], 2) ?></td>
            <td><?= htmlspecialchars($gasto['notas'] ?? '') ?></td>
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