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
    <div class="col-md-4 position-relative">
        <label class="form-label">Proveedor</label>
        <input type="hidden" name="proveedor_id" id="gastos-proveedor-id" value="<?= htmlspecialchars($proveedorId ?? '') ?>">
        <input
            type="text"
            class="form-control"
            autocomplete="off"
            data-proveedor-search
            data-url="<?= url('/proveedores/buscar') ?>"
            value="<?= htmlspecialchars($proveedorLabel ?? '') ?>"
            placeholder="Buscar por nombre o documento"
        >
        <div id="gastos-proveedor-sugerencias" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050;"></div>
        <div class="form-text">Escribe al menos dos caracteres para ver sugerencias.</div>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-secondary">Buscar</button>
        <a class="btn btn-link" href="<?= url('/gastos') ?>">Limpiar</a>
    </div>
</form>
<div class="table-responsive">
    <table class="table table-striped align-middle">
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
                    <td><?= htmlspecialchars($gasto['concepto_nombre'] ?? $gasto['concepto']) ?></td>
                    <td><?= htmlspecialchars($gasto['proveedor_nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($gasto['nro_factura'] ?? '') ?></td>
                    <td>Gs <?= number_format((float)$gasto['monto'], 0, ',', '.') ?></td>
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
</div>
<script>
    (function() {
        const searchInput = document.querySelector('[data-proveedor-search]');
        const hiddenInput = document.getElementById('gastos-proveedor-id');
        const suggestions = document.getElementById('gastos-proveedor-sugerencias');

        if (!searchInput || !hiddenInput || !suggestions) return;

        let abortController = null;
        let selectedLabel = searchInput.value;

        const clearSuggestions = () => {
            suggestions.innerHTML = '';
            suggestions.classList.add('d-none');
        };

        const buildLabel = (item) => {
            const details = [];
            if (item.documento) details.push(item.documento);
            if (item.telefono) details.push(item.telefono);
            return [item.nombre, ...details].filter(Boolean).join(' · ');
        };

        const setProveedor = (proveedor) => {
            if (!proveedor || !proveedor.id) return;
            const label = buildLabel(proveedor);
            hiddenInput.value = proveedor.id;
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
                button.dataset.id = item.id;
                button.dataset.label = buildLabel(item);

                const title = document.createElement('div');
                title.className = 'fw-semibold';
                title.textContent = item.nombre;
                button.appendChild(title);

                const details = [];
                if (item.documento) details.push(item.documento);
                if (item.telefono) details.push(item.telefono);
                if (details.length) {
                    const small = document.createElement('small');
                    small.className = 'text-muted';
                    small.textContent = details.join(' · ');
                    button.appendChild(small);
                }

                button.addEventListener('click', () => setProveedor(item));
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