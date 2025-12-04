<?php $title = 'Citas'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Citas</h1>
    <a href="<?= url('/citas/crear') ?>" class="btn btn-primary">Agendar</a>
</div>
<form class="row g-3 mb-3 align-items-end" method="get" action="<?= url('/citas') ?>">
    <div class="col-md-4 col-lg-3">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="fecha_ini" class="form-control" value="<?= htmlspecialchars($fechaIni ?? date('Y-m-d')) ?>">
    </div>
    <div class="col-md-4 col-lg-3">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin ?? date('Y-m-d')) ?>">
    </div>
    <div class="col-md-6 col-lg-4 position-relative">
        <label class="form-label">Cliente</label>
        <input type="hidden" name="cliente_id" id="filtro-cliente-id" value="<?= htmlspecialchars($clienteId ?? '') ?>">
        <input
            type="text"
            class="form-control"
            autocomplete="off"
            data-cliente-search
            data-url="<?= url('/clientes/buscar') ?>"
            value="<?= htmlspecialchars($clienteLabel ?? '') ?>"
            placeholder="Buscar por nombre, teléfono o correo"
        >
        <div id="filtro-cliente-sugerencias" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050;"></div>
        <div class="form-text">Escribe al menos dos caracteres para ver sugerencias.</div>
    </div>
    <div class="col-md-2 col-lg-2">
        <button class="btn btn-secondary">Filtrar</button>
        <a class="btn btn-link" href="<?= url('/citas') ?>">Hoy</a>
    </div>
</form>
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Horario</th>
                <th>Cliente</th>
                <th>Funcionario</th>
                <th>Servicios</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($citas as $cita): ?>
            <?php
                $cliente = array_values(array_filter($clientes, fn($c) => $c['id'] == $cita['cliente_id']))[0] ?? null;
                $funcionario = array_values(array_filter($funcionarios, fn($f) => $f['id'] == $cita['funcionario_id']))[0] ?? null;
                $detalleServicios = $serviciosPorCita[$cita['id']] ?? [];
            ?>
            <tr>
                <td><?= htmlspecialchars($cita['fecha']) ?></td>
                <td><?= htmlspecialchars(substr($cita['hora_inicio'], 0, 5)) ?> - <?= htmlspecialchars(substr($cita['hora_fin'], 0, 5)) ?></td>
                <td><?= htmlspecialchars($cliente['nombre'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($funcionario['nombre'] ?? 'N/A') ?></td>
                <td>
                    <?php if (!empty($detalleServicios)): ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($detalleServicios as $detalle): ?>
                                <li><?= htmlspecialchars($detalle['nombre']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <span class="text-muted">Sin servicios</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($cita['estado']) ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-secondary" href="<?= url('/citas/editar?id=' . $cita['id']) ?>">Editar</a>
                    <form action="<?= url('/citas/eliminar?id=' . $cita['id']) ?>" method="post" class="d-inline">
                        <button class="btn btn-sm btn-danger" onclick="return confirm('¿Cancelar cita?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    (function() {
        const searchInput = document.querySelector('[data-cliente-search]');
        const hiddenInput = document.getElementById('filtro-cliente-id');
        const suggestions = document.getElementById('filtro-cliente-sugerencias');

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