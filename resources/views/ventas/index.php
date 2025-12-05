<?php $title = 'Ventas'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Ventas</h1>
    <a href="<?= url('/ventas/crear') ?>" class="btn btn-primary">Nueva</a>
</div>
<form class="row g-3 align-items-end mb-4" method="get" action="<?= url('/ventas') ?>">
    <div class="col-md-3 col-lg-2">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="fecha_ini" class="form-control" value="<?= htmlspecialchars($fechaIni ?? '') ?>">
    </div>
    <div class="col-md-3 col-lg-2">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin ?? '') ?>">
    </div>
    <div class="col-md-6 col-lg-5 position-relative">
        <label class="form-label">Cliente</label>
        <input type="hidden" name="cliente_id" id="ventas-cliente-id" value="<?= htmlspecialchars($clienteId ?? '') ?>">
        <input
            type="text"
            class="form-control"
            autocomplete="off"
            data-cliente-search
            data-url="<?= url('/clientes/buscar') ?>"
            value="<?= htmlspecialchars($clienteLabel ?? '') ?>"
            placeholder="Buscar por nombre, teléfono o correo"
        >
        <div id="ventas-cliente-sugerencias" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050;"></div>
        <div class="form-text">Escribe al menos dos caracteres para ver sugerencias.</div>
    </div>
    <div class="col-md-3 col-lg-3 d-flex gap-2">
        <button class="btn btn-secondary">Buscar</button>
        <a class="btn btn-link" href="<?= url('/ventas') ?>">Limpiar</a>
    </div>
</form>
<?php include __DIR__ . '/ticket_template.php'; ?>
<div class="table-responsive">
    <table class="table table-striped align-middle">
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
                <td>Gs <?= number_format((float)$venta['monto_total'], 0, ',', '.') ?></td>
                <td>Gs <?= number_format((float)$venta['descuento'], 0, ',', '.') ?></td>
                <td>Gs <?= number_format((float)$venta['monto_pagado'], 0, ',', '.') ?></td>
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
</div>
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
    (function() {
        const searchInput = document.querySelector('[data-cliente-search]');
        const hiddenInput = document.getElementById('ventas-cliente-id');
        const suggestions = document.getElementById('ventas-cliente-sugerencias');

        if (!searchInput || !hiddenInput || !suggestions) return;

        let abortController = null;
        let selectedLabel = searchInput.value;

        const clearSuggestions = () => {
            suggestions.innerHTML = '';
            suggestions.classList.add('d-none');
        };

        const buildLabel = (item) => {
            const parts = [item.nombre];
            if (item.telefono) parts.push(item.telefono);
            if (item.email) parts.push(item.email);
            return parts.filter(Boolean).join(' · ');
        };

        const setCliente = (cliente) => {
            if (!cliente || !cliente.id) return;
            const label = buildLabel(cliente);
            hiddenInput.value = cliente.id;
            searchInput.value = label;
            selectedLabel = label;
            clearSuggestions();
        };

        const renderSuggestions = (items) => {
            clearSuggestions();
            if (!items.length) return;

            items.forEach((item) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'list-group-item list-group-item-action';
                button.textContent = buildLabel(item);
                button.addEventListener('click', () => setCliente(item));
                suggestions.appendChild(button);
            });

            suggestions.classList.remove('d-none');
        };

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            hiddenInput.value = '';

            if (abortController) {
                abortController.abort();
                abortController = null;
            }

            if (query.length < 2) {
                clearSuggestions();
                return;
            }

            abortController = new AbortController();
            fetch(`${searchInput.dataset.url}?q=${encodeURIComponent(query)}`, { signal: abortController.signal })
                .then(res => res.ok ? res.json() : [])
                .then(data => renderSuggestions(Array.isArray(data) ? data : []))
                .catch(() => {});
        });

        searchInput.addEventListener('blur', () => {
            setTimeout(clearSuggestions, 150);
            if (!hiddenInput.value) searchInput.value = '';
        });

        searchInput.addEventListener('focus', () => {
            if (selectedLabel) searchInput.value = selectedLabel;
        });
    })();
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>