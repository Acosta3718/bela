<?php $title = 'Registrar pago a funcionario'; $errors = $errors ?? []; $pago = $pago ?? []; ob_start(); ?>
<?php
    $periodoInicio = $periodoInicio ?? ($pago['periodo_inicio'] ?? date('Y-m-d'));
    $periodoFin = $periodoFin ?? ($pago['periodo_fin'] ?? date('Y-m-d'));
    $funcionarioId = $funcionarioId ?? ($pago['funcionario_id'] ?? '');
    $ventasIniciales = array_map(function ($venta) {
        return [
            'id' => (int)$venta['id'],
            'cita_fecha' => $venta['cita_fecha'] ?? null,
            'monto_total' => $venta['monto_total'] ?? 0,
            'monto_pagado' => $venta['monto_pagado'] ?? 0,
            'porcentaje_comision' => (float)($venta['porcentaje_comision'] ?? 0),
        ];
    }, $ventas ?? []);
    $seleccionadas = $pago['venta_ids'] ?? [];
?>
<h1 class="h3 mb-4">Registrar pago a funcionario</h1>
<form method="post" action="<?= url('/pagos') ?>" id="pago-form">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Funcionario</label>
            <select name="funcionario_id" id="funcionario_id" class="form-select">
                <option value="">Seleccione</option>
                <?php foreach ($funcionarios as $item): ?>
                    <option value="<?= $item['id'] ?>" <?= ($funcionarioId == $item['id']) ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['funcionario_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['funcionario_id']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-3">
            <label class="form-label">Periodo inicio</label>
            <input type="date" name="periodo_inicio" id="periodo_inicio" class="form-control" value="<?= htmlspecialchars($periodoInicio) ?>">
            <?php if (!empty($errors['periodo_inicio'])): ?><div class="text-danger small"><?= implode(', ', $errors['periodo_inicio']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-3">
            <label class="form-label">Periodo fin</label>
            <input type="date" name="periodo_fin" id="periodo_fin" class="form-control" value="<?= htmlspecialchars($periodoFin) ?>">
            <?php if (!empty($errors['periodo_fin'])): ?><div class="text-danger small"><?= implode(', ', $errors['periodo_fin']) ?></div><?php endif; ?>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 mb-3">
        <button type="button" class="btn btn-outline-secondary" id="filtrar-ventas">Filtrar ventas cobradas</button>
        <span class="text-muted small">Seleccione funcionario y rango para listar ventas pagadas.</span>
    </div>

    <div class="mb-3">
        <label class="form-label">Ventas cobradas</label>
        <div class="border rounded p-2" style="max-height: 260px; overflow:auto;" id="ventas-container" data-url="<?= url('/pagos/ventas') ?>">
            <div class="text-muted" id="ventas-placeholder">Seleccione un funcionario y rango de fechas para ver ventas.</div>
        </div>
        <?php if (!empty($errors['venta_ids'])): ?><div class="text-danger small"><?= implode(', ', $errors['venta_ids']) ?></div><?php endif; ?>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Monto a pagar</label>
            <input type="number" step="0.01" name="monto" id="monto" class="form-control" value="<?= htmlspecialchars($pago['monto'] ?? '') ?>" readonly>
            <div class="form-text">Suma de las comisiones de las ventas seleccionadas.</div>
            <?php if (!empty($errors['monto'])): ?><div class="text-danger small"><?= implode(', ', $errors['monto']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha de pago</label>
            <input type="date" name="fecha_pago" class="form-control" value="<?= htmlspecialchars($pago['fecha_pago'] ?? date('Y-m-d')) ?>">
            <?php if (!empty($errors['fecha_pago'])): ?><div class="text-danger small"><?= implode(', ', $errors['fecha_pago']) ?></div><?php endif; ?>
        </div>
    </div>

    <div class="mb-3 mt-3">
        <label class="form-label">Notas</label>
        <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($pago['notas'] ?? '') ?></textarea>
    </div>
    <button class="btn btn-primary">Guardar</button>
    <a href="<?= url('/pagos') ?>" class="btn btn-link">Cancelar</a>
</form>

<script>
    (function() {
        const ventasContainer = document.getElementById('ventas-container');
        const montoInput = document.getElementById('monto');
        const funcionarioSelect = document.getElementById('funcionario_id');
        const inicioInput = document.getElementById('periodo_inicio');
        const finInput = document.getElementById('periodo_fin');
        const filtrarBtn = document.getElementById('filtrar-ventas');
        const ventasIniciales = <?= json_encode($ventasIniciales) ?>;
        const seleccionInicial = new Set(<?= json_encode(array_map('intval', $seleccionadas)) ?>);

        function toMoney(number) {
            const value = Number(number || 0);
            return value.toLocaleString('es-PY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function calcularComision(venta) {
            const base = venta.monto_pagado && Number(venta.monto_pagado) > 0
                ? Number(venta.monto_pagado)
                : Number(venta.monto_total || 0);
            const porcentaje = Number(venta.porcentaje_comision || 0);
            return {
                base,
                porcentaje,
                comision: base * (porcentaje / 100),
            };
        }

        function renderVentas(listado) {
            ventasContainer.innerHTML = '';
            if (!listado.length) {
                const info = document.createElement('div');
                info.className = 'text-muted';
                info.textContent = funcionarioSelect.value
                    ? 'No hay ventas cobradas en el rango seleccionado.'
                    : 'Seleccione funcionario y rango de fechas para ver ventas.';
                ventasContainer.appendChild(info);
                return;
            }

            listado.forEach((venta) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'form-check';

                const infoComision = calcularComision(venta);
                const input = document.createElement('input');
                input.type = 'checkbox';
                input.className = 'form-check-input venta-option';
                input.name = 'venta_ids[]';
                input.value = venta.id;
                input.id = `venta-${venta.id}`;
                input.dataset.comision = infoComision.comision;
                input.checked = seleccionInicial.has(venta.id);
                input.addEventListener('change', actualizarMonto);

                const label = document.createElement('label');
                label.className = 'form-check-label';
                label.setAttribute('for', input.id);
                const fecha = venta.cita_fecha ? ` - ${venta.cita_fecha}` : '';
                label.textContent = `Venta #${venta.id}${fecha} · Venta $${toMoney(infoComision.base)} · Comisión ${infoComision.porcentaje}%: $${toMoney(infoComision.comision)}`;

                wrapper.appendChild(input);
                wrapper.appendChild(label);
                ventasContainer.appendChild(wrapper);
            });

            actualizarMonto();
        }

        function actualizarMonto() {
            const checks = ventasContainer.querySelectorAll('.venta-option:checked');
            let total = 0;
            checks.forEach((cb) => {
                total += Number(cb.dataset.comision || 0);
            });
            montoInput.value = total ? total.toFixed(2) : '';
        }

        function construirQuery() {
            const params = new URLSearchParams();
            if (funcionarioSelect.value) params.append('funcionario_id', funcionarioSelect.value);
            if (inicioInput.value) params.append('desde', inicioInput.value);
            if (finInput.value) params.append('hasta', finInput.value);
            return params.toString();
        }

        function cargarVentas() {
            const funcionario = funcionarioSelect.value;
            if (!funcionario || !inicioInput.value || !finInput.value) {
                ventasContainer.innerHTML = '';
                const msg = document.createElement('div');
                msg.className = 'text-muted';
                msg.textContent = 'Seleccione funcionario y rango de fechas para ver ventas.';
                ventasContainer.appendChild(msg);
                actualizarMonto();
                return;
            }

            const url = `${ventasContainer.dataset.url}?${construirQuery()}`;
            fetch(url)
                .then((res) => res.ok ? res.json() : [])
                .then((data) => {
                    seleccionInicial.clear();
                    const ventas = Array.isArray(data) ? data : [];
                    renderVentas(ventas);
                })
                .catch(() => {
                    ventasContainer.innerHTML = '<div class="text-danger">No se pudieron cargar las ventas.</div>';
                    montoInput.value = '';
                });
        }

        filtrarBtn.addEventListener('click', cargarVentas);
        funcionarioSelect.addEventListener('change', () => {
            seleccionInicial.clear();
        });

        renderVentas(ventasIniciales);
        if (!ventasIniciales.length && funcionarioSelect.value && inicioInput.value && finInput.value) {
            cargarVentas();
        }
    })();
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>