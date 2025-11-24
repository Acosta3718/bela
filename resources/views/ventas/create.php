<?php $title = 'Registrar venta'; $errors = $errors ?? []; $venta = $venta ?? []; ob_start(); ?>
<h1 class="h3 mb-3">Registrar venta</h1>
<form class="row g-3 align-items-end mb-4" method="get" action="<?= url('/ventas/crear') ?>">
    <div class="col-md-6">
        <label class="form-label">Filtrar por cliente</label>
        <select name="cliente_id" class="form-select">
            <option value="">Todos</option>
            <?php foreach (($clientes ?? []) as $cliente): ?>
                <option value="<?= $cliente['id'] ?>" <?= ($clienteId ?? null) == $cliente['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <button class="btn btn-secondary">Aplicar filtro</button>
        <a class="btn btn-link" href="<?= url('/ventas/crear') ?>">Limpiar</a>
    </div>
</form>
<form method="post" action="<?= url('/ventas') ?>" id="venta-form">
    <?php include __DIR__ . '/form.php'; ?>
</form>
<?php include __DIR__ . '/form_scripts.php'; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>