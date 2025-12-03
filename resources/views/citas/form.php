<?php
$errors = $errors ?? [];
$cita = $cita ?? [];
$clientes = $clientes ?? [];
$funcionarios = $funcionarios ?? [];
$servicios = $servicios ?? [];

$clienteSeleccionado = '';
if (!empty($cita['cliente_id'])) {
    foreach ($clientes as $cliente) {
        if ((int)$cliente['id'] === (int)($cita['cliente_id'] ?? 0)) {
            $detalles = array_filter([
                $cliente['telefono'] ?? '',
                $cliente['email'] ?? '',
            ]);
            $clienteSeleccionado = trim($cliente['nombre'] . (!empty($detalles) ? ' · ' . implode(' · ', $detalles) : ''));
            break;
        }
    }
}

$detalleServicios = $cita['servicios'] ?? [];
if (empty($detalleServicios)) {
    $detalleServicios = [''];
}

$serviciosPorId = [];
foreach ($servicios as $servicio) {
    $serviciosPorId[(int)$servicio['id']] = $servicio;
}

$formatPrecio = function ($valor) {
    if ($valor === null || $valor === '') {
        return '—';
    }
    return 'Gs ' . number_format((float)$valor, 0, ',', '.');
};
?>
<div class="mb-3 position-relative">
     <label class="form-label">Cliente</label>
     <div class="input-group">
        <input type="hidden" name="cliente_id" id="cliente-id" value="<?= htmlspecialchars($cita['cliente_id'] ?? '') ?>">
        <input type="text" class="form-control" autocomplete="off" data-cliente-search data-url="<?= url('/clientes/buscar') ?>" value="<?= htmlspecialchars($clienteSeleccionado) ?>" placeholder="Buscar por nombre, teléfono o correo">
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-nuevo-cliente">Nuevo cliente</button>
     </div>
    <div id="cliente-sugerencias" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050;"></div>
    <?php if (!empty($errors['cliente_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['cliente_id']) ?></div><?php endif; ?>
    <div class="form-text">Escribe al menos dos caracteres para ver sugerencias.</div>
</div>
<div class="mb-3">
    <label class="form-label">Funcionario</label>
    <select name="funcionario_id" class="form-select">
        <option value="">Seleccione</option>
        <?php foreach ($funcionarios as $item): ?>
            <option value="<?= $item['id'] ?>" <?= (($cita['funcionario_id'] ?? '') == $item['id']) ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($errors['funcionario_id'])): ?><div class="text-danger small"><?= implode(', ', $errors['funcionario_id']) ?></div><?php endif; ?>
</div>
<div class="mb-3">
    <label class="form-label">Servicios</label>
    <div class="card">
        <div class="card-body">
            <div id="servicios-detalle">
                <?php foreach ($detalleServicios as $servicioId): ?>
                <div class="detalle-servicio row g-2 align-items-center mb-2">
                    <div class="col">
                        <select name="servicios[]" class="form-select">
                            <option value="">Seleccione</option>
                            <?php foreach ($servicios as $item): ?>
                                <option value="<?= $item['id'] ?>" data-precio="<?= htmlspecialchars($item['precio_base'] ?? '') ?>" data-precio-label="<?= htmlspecialchars($formatPrecio($item['precio_base'] ?? '')) ?>" <?= ((string)$servicioId !== '' && (int)$servicioId === (int)$item['id']) ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?> (<?= $formatPrecio($item['precio_base'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <span class="text-muted small" data-servicio-precio><?= isset($serviciosPorId[(int)$servicioId]) ? $formatPrecio($serviciosPorId[(int)$servicioId]['precio_base'] ?? '') : '—' ?></span>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-danger btn-sm eliminar-servicio" title="Quitar">&times;</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="agregar-servicio">Agregar servicio</button>
        </div>
    </div>  
    <?php if (!empty($errors['servicios'])): ?><div class="text-danger small mt-2"><?= implode(', ', $errors['servicios']) ?></div><?php endif; ?>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($cita['fecha'] ?? date('Y-m-d')) ?>">
            <?php if (!empty($errors['fecha'])): ?><div class="text-danger small"><?= implode(', ', $errors['fecha']) ?></div><?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Hora inicio</label>
            <input type="time" name="hora_inicio" class="form-control" value="<?= htmlspecialchars(substr($cita['hora_inicio'] ?? '', 0, 5)) ?>">
            <?php if (!empty($errors['hora_inicio'])): ?><div class="text-danger small"><?= implode(', ', $errors['hora_inicio']) ?></div><?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Hora fin</label>
            <input type="time" name="hora_fin" class="form-control" value="<?= htmlspecialchars(substr($cita['hora_fin'] ?? '', 0, 5)) ?>">
            <?php if (!empty($errors['hora_fin'])): ?><div class="text-danger small"><?= implode(', ', $errors['hora_fin']) ?></div><?php endif; ?>
        </div>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Estado</label>
    <select name="estado" class="form-select">
        <?php $estado = $cita['estado'] ?? 'pendiente'; ?>
        <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
        <option value="confirmada" <?= $estado === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
        <option value="completada" <?= $estado === 'completada' ? 'selected' : '' ?>>Completada</option>
        <option value="cancelada" <?= $estado === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Notas</label>
    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($cita['notas'] ?? '') ?></textarea>
</div>
<template id="servicio-row-template">
    <div class="detalle-servicio row g-2 align-items-center mb-2">
        <div class="col">
            <select name="servicios[]" class="form-select" data-servicio-select>
                <option value="">Seleccione</option>
                <?php foreach ($servicios as $item): ?>
                    <option value="<?= $item['id'] ?>" data-precio="<?= htmlspecialchars($item['precio_base'] ?? '') ?>" data-precio-label="<?= htmlspecialchars($formatPrecio($item['precio_base'] ?? '')) ?>"><?= htmlspecialchars($item['nombre']) ?> (<?= $formatPrecio($item['precio_base'] ?? '') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <span class="text-muted small" data-servicio-precio>—</span>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-outline-danger btn-sm eliminar-servicio" title="Quitar">&times;</button>
        </div>
    </div>
</template>

<script>
(function() {
    const searchInput = document.querySelector('[data-cliente-search]');
    const hiddenInput = document.getElementById('cliente-id');
    const suggestions = document.getElementById('cliente-sugerencias');

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
        const parts = [item.nombre];
        if (item.telefono) {
            parts.push(item.telefono);
        }
        if (item.email) {
            parts.push(item.email);
        }
        return parts.filter(Boolean).join(' · ');
    };

    const asignarCliente = (cliente) => {
        if (!cliente || !cliente.id) {
            return;
        }
        const label = cliente.label || buildLabel(cliente);
        hiddenInput.value = cliente.id;
        searchInput.value = label;
        selectedLabel = label;
        clearSuggestions();
    };

    window.belaClienteSelector = {
        set: asignarCliente,
        buildLabel
    };

    const renderSuggestions = (items) => {
        clearSuggestions();
        if (!items.length) {
            return;
        }

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
            if (item.telefono) {
                details.push(item.telefono);
            }
            if (item.email) {
                details.push(item.email);
            }
            if (details.length) {
                const info = document.createElement('small');
                info.className = 'text-muted';
                info.textContent = details.join(' · ');
                button.appendChild(info);
            }

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
        fetch(this.dataset.url + '?q=' + encodeURIComponent(query), { signal: abortController.signal })
            .then((response) => response.ok ? response.json() : [])
            .then((data) => {
                renderSuggestions(Array.isArray(data) ? data : []);
            })
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    clearSuggestions();
                }
            });
    });

    suggestions.addEventListener('click', function(event) {
        const button = event.target.closest('button[data-id]');
        if (!button) {
            return;
        }
        asignarCliente({ id: button.dataset.id, label: button.dataset.label });
    });

    document.addEventListener('click', function(event) {
        if (!suggestions.contains(event.target) && event.target !== searchInput) {
            clearSuggestions();
        }
    });

    searchInput.addEventListener('blur', function() {
        if (this.value !== selectedLabel) {
            hiddenInput.value = '';
        }
    });
})();

(function() {
    const container = document.getElementById('servicios-detalle');
    const template = document.getElementById('servicio-row-template');
    const addButton = document.getElementById('agregar-servicio');

    if (!container || !template || !addButton) {
        return;
    }

    const actualizarBotones = () => {
        const botones = container.querySelectorAll('.eliminar-servicio');
        const deshabilitar = botones.length <= 1;
        botones.forEach((boton) => {
            boton.disabled = deshabilitar;
        });
    };

    const actualizarPrecio = (select) => {
        if (!select) {
            return;
        }
        const precioLabel = select.selectedOptions[0]?.dataset.precioLabel || '—';
        const etiqueta = select.closest('.detalle-servicio')?.querySelector('[data-servicio-precio]');
        if (etiqueta) {
            etiqueta.textContent = precioLabel;
        }
    };

    const prepararSelect = (select) => {
        if (!select) {
            return;
        }
        actualizarPrecio(select);
        select.addEventListener('change', () => actualizarPrecio(select));
    };
    
    container.querySelectorAll('[data-servicio-select]').forEach(prepararSelect);

    actualizarBotones();

    addButton.addEventListener('click', () => {
        const fragment = template.content.cloneNode(true);
        const nuevoSelect = fragment.querySelector('[data-servicio-select]');
        container.appendChild(fragment);
        actualizarBotones();
        actualizarPrecio(nuevoSelect);
    });

    container.addEventListener('click', (event) => {
        if (event.target.classList.contains('eliminar-servicio')) {
            const fila = event.target.closest('.detalle-servicio');
            if (fila) {
                fila.remove();
                actualizarBotones();
            }
        }
    });

    container.addEventListener('change', (event) => {
        if (event.target.matches('[data-servicio-select]')) {
            actualizarPrecio(event.target);
        }
    });
})();
</script>