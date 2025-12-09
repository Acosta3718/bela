<div class="mb-3">
    <label class="form-label">Fecha</label>
    <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($gasto['fecha'] ?? date('Y-m-d')) ?>">
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
    <div class="input-group">
        <input type="hidden" name="concepto_id" id="concepto-id" value="<?= htmlspecialchars($gasto['concepto_id'] ?? '') ?>">
        <input type="text" name="concepto" class="form-control" autocomplete="off" data-concepto-search
               data-url="<?= url('/conceptos/buscar') ?>" value="<?= htmlspecialchars($gasto['concepto'] ?? '') ?>"
               placeholder="Buscar o escribir concepto">
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-nuevo-concepto">Nuevo concepto</button>
    </div>
    <div id="concepto-sugerencias" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050;"></div>
    <?php if (!empty($errors['concepto'])): ?><div class="text-danger small"><?= implode(', ', $errors['concepto']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Monto</label>
    <input type="number" step="0.01" name="monto" class="form-control" value="<?= htmlspecialchars($gasto['monto'] ?? '') ?>">
    <?php if (!empty($errors['monto'])): ?><div class="text-danger small"><?= implode(', ', $errors['monto']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Cuenta</label>
    <select name="cuenta_id" class="form-select">
        <option value="">Seleccione</option>
        <?php foreach ($cuentas ?? [] as $cuenta): ?>
            <option value="<?= $cuenta['id'] ?>" <?= ($gasto['cuenta_id'] ?? '') == $cuenta['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cuenta['nombre']) ?> (Gs <?= number_format((float)($cuenta['saldo'] ?? 0), 0, ',', '.') ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($errors['cuenta_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['cuenta_id']) ?></div><?php endif; ?>
    <div class="form-text">Si selecciona una cuenta, el monto se descontará automáticamente.</div>
</div>
<div class="mb-3">
    <label class="form-label">Notas</label>
    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($gasto['notas'] ?? '') ?></textarea>
</div>

<div class="modal fade" id="modal-nuevo-concepto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo concepto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nuevo-concepto-nombre" maxlength="150">
                    <div class="text-danger small d-none" id="nuevo-concepto-error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardar-concepto">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const proveedorSearchInput = document.querySelector('[data-proveedor-search]');
    const proveedorHiddenInput = document.getElementById('proveedor-id');
    const proveedorSuggestions = document.getElementById('proveedor-sugerencias');

    const conceptoSearchInput = document.querySelector('[data-concepto-search]');
    const conceptoHiddenInput = document.getElementById('concepto-id');
    const conceptoSuggestions = document.getElementById('concepto-sugerencias');

    const modalConcepto = document.getElementById('modal-nuevo-concepto');
    const modalError = document.getElementById('nuevo-concepto-error');
    const modalNombre = document.getElementById('nuevo-concepto-nombre');
    const btnGuardarConcepto = document.getElementById('guardar-concepto');

    const buildProveedorLabel = (item) => {
        const details = [];
        if (item.documento) details.push(item.documento);
        if (item.telefono) details.push(item.telefono);
        return [item.nombre, ...details].filter(Boolean).join(' · ');
    };

    const clearList = (element) => {
        element.innerHTML = '';
        element.classList.add('d-none');
    };

    const setupSearch = (input, hidden, list, labelBuilder) => {
        if (!input || !hidden || !list) return () => {};

        let abortController = null;
        let selectedLabel = input.value;

        const render = (items) => {
            clearList(list);
            if (!items.length) return;

            items.forEach((item) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'list-group-item list-group-item-action';
                button.textContent = labelBuilder(item);
                button.addEventListener('click', () => {
                    hidden.value = item.id;
                    input.value = labelBuilder(item);
                    selectedLabel = input.value;
                    clearList(list);
                });
                list.appendChild(button);
            });

            list.classList.remove('d-none');
        };

        input.addEventListener('input', function() {
            const query = this.value.trim();
            hidden.value = '';

            if (abortController) {
                abortController.abort();
                abortController = null;
            }

            if (query.length < 2) {
                clearList(list);
                return;
            }

            abortController = new AbortController();
            fetch(`${input.dataset.url}?q=${encodeURIComponent(query)}`, { signal: abortController.signal })
                .then(res => res.ok ? res.json() : [])
                .then(data => render(Array.isArray(data) ? data : []))
                .catch(() => {});
        });

        input.addEventListener('blur', () => {
            setTimeout(() => clearList(list), 150);
            if (!hidden.value) {
                input.value = '';
            }
        });

        input.addEventListener('focus', () => {
            if (selectedLabel) {
                input.value = selectedLabel;
            }
        });

        return () => clearList(list);
    };

    setupSearch(proveedorSearchInput, proveedorHiddenInput, proveedorSuggestions, buildProveedorLabel);
    setupSearch(
        conceptoSearchInput,
        conceptoHiddenInput,
        conceptoSuggestions,
        (item) => item.nombre
    );

    if (modalConcepto && modalNombre && btnGuardarConcepto) {
        const modal = new bootstrap.Modal(modalConcepto);
        modalConcepto.addEventListener('shown.bs.modal', () => {
            modalNombre.value = '';
            modalError.classList.add('d-none');
            modalNombre.focus();
        });

        btnGuardarConcepto.addEventListener('click', () => {
            const nombre = modalNombre.value.trim();
            if (!nombre) {
                modalError.textContent = 'Ingrese un nombre para el concepto';
                modalError.classList.remove('d-none');
                return;
            }

            fetch('<?= url('/conceptos/inline') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ nombre }).toString(),
            })
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then((concepto) => {
                    if (!concepto || !concepto.id) return;
                    conceptoHiddenInput.value = concepto.id;
                    if (conceptoSearchInput) {
                        conceptoSearchInput.value = concepto.nombre;
                    }
                    modal.hide();
                })
                .catch(async (err) => {
                    try {
                        const data = await err.json();
                        const mensajes = data.errors?.nombre?.join(', ') || 'No se pudo guardar el concepto';
                        modalError.textContent = mensajes;
                        modalError.classList.remove('d-none');
                    } catch (e) {
                        modalError.textContent = 'No se pudo guardar el concepto';
                        modalError.classList.remove('d-none');
                    }
                });
        });
    }
})();
</script>