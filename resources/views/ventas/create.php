<?php $title = 'Registrar venta'; $errors = $errors ?? []; $venta = $venta ?? []; ob_start(); ?>
<h1 class="h3 mb-3">Registrar venta</h1>
<form class="row g-3 align-items-end mb-4" method="get" action="<?= url('/ventas/crear') ?>">
     <div class="col-md-6 position-relative">
        <label class="form-label">Buscar por cliente</label>
        <input type="hidden" name="cliente_id" id="filtro-cliente-id" value="<?= htmlspecialchars($clienteId ?? '') ?>">
        <input type="text" class="form-control" autocomplete="off" data-cliente-search data-url="<?= url('/clientes/buscar') ?>" value="<?= htmlspecialchars($clienteLabel ?? '') ?>" placeholder="Buscar por nombre, teléfono o correo">
        <div id="filtro-cliente-sugerencias" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050;"></div>
        <div class="form-text">Escribe al menos dos caracteres para ver sugerencias.</div>
    </div>
    <div class="col-md-3">
        <button class="btn btn-secondary">Aplicar filtro</button>
        <a class="btn btn-link" href="<?= url('/ventas/crear') ?>">Limpiar</a>
    </div>
</form>
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
                button.dataset.id = item.id;
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
<form method="post" action="<?= url('/ventas') ?>" id="venta-form">
    <?php include __DIR__ . '/form.php'; ?>
</form>
<?php include __DIR__ . '/ticket_template.php'; ?>
<?php include __DIR__ . '/form_scripts.php'; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>