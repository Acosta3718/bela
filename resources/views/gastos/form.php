<div class="mb-3">
    <label class="form-label">Fecha</label>
    <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($gasto['fecha'] ?? '') ?>">
    <?php if (!empty($errors['fecha'])): ?><div class="text-danger small"><?= implode(', ', $errors['fecha']) ?></div><?php endif; ?>
</div>
<div class="mb-3 position-relative">
    <label class="form-label">Proveedor</label>
    <input type="hidden" name="proveedor_id" id="proveedor-id" value="<?= htmlspecialchars($gasto['proveedor_id'] ?? '') ?>">
    <input type="text" class="form-control" autocomplete="off" data-proveedor-search data-url="<?= url('/proveedores/buscar') ?>"
        value="<?= htmlspecialchars($gasto['proveedor_nombre'] ?? $gasto['proveedor_label'] ?? '') ?>" placeholder="Buscar por nombre o documento">
    <div id="proveedor-sugerencias" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050;"></div>
    <?php if (!empty($errors['proveedor_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['proveedor_id']) ?></div><?php endif; ?>
    <div class="form-text">Escribe al menos dos caracteres para ver sugerencias.</div>
</div>
<div class="mb-3">
    <label class="form-label">Nro. factura</label>
    <input type="text" name="nro_factura" class="form-control" value="<?= htmlspecialchars($gasto['nro_factura'] ?? '') ?>">
    <?php if (!empty($errors['nro_factura'])): ?><div class="text-danger small"><?= implode(', ', $errors['nro_factura']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Concepto</label>
    <input type="text" name="concepto" class="form-control" value="<?= htmlspecialchars($gasto['concepto'] ?? '') ?>">
    <?php if (!empty($errors['concepto'])): ?><div class="text-danger small"><?= implode(', ', $errors['concepto']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Monto</label>
    <input type="number" step="0.01" name="monto" class="form-control" value="<?= htmlspecialchars($gasto['monto'] ?? '') ?>">
    <?php if (!empty($errors['monto'])): ?><div class="text-danger small"><?= implode(', ', $errors['monto']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Notas</label>
    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($gasto['notas'] ?? '') ?></textarea>
</div><script>
(function() {
    const searchInput = document.querySelector('[data-proveedor-search]');
    const hiddenInput = document.getElementById('proveedor-id');
    const suggestions = document.getElementById('proveedor-sugerencias');

    if (!searchInput || !hiddenInput || !suggestions) {
        return;
    }

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
        if (!hiddenInput.value) {
            searchInput.value = '';
        }
    });

    searchInput.addEventListener('focus', () => {
        if (selectedLabel) {
            searchInput.value = selectedLabel;
        }
    });
})();
</script>