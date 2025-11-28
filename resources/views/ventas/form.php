<div class="mb-3">
    <label class="form-label">Citas</label>
    <div class="border rounded p-2" style="max-height: 260px; overflow:auto;">
        <?php foreach ($citas as $item): ?>
            <?php
                $checked = in_array($item['id'], $venta['cita_ids'] ?? []);
                $hora = substr($item['hora_inicio'] ?? '', 0, 5);
                $totalServicios = (float)($item['total_servicios'] ?? 0);
                $etiqueta = sprintf('%s %s - %s ($%0.2f)', $item['fecha'], $hora, $item['cliente'], $totalServicios);
            ?>
            <div class="form-check">
                <input class="form-check-input cita-option" id="cita-<?= $item['id'] ?>" type="checkbox" name="cita_ids[]" value="<?= $item['id'] ?>"
                    data-total="<?= $totalServicios ?>"
                    data-fecha="<?= htmlspecialchars($item['fecha']) ?>"
                    data-hora="<?= htmlspecialchars($hora) ?>"
                    data-cliente="<?= htmlspecialchars($item['cliente']) ?>"
                    <?= $checked ? 'checked' : '' ?>>
                <label class="form-check-label" for="cita-<?= $item['id'] ?>">#<?= $item['id'] ?> - <?= htmlspecialchars($etiqueta) ?></label>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($errors['cita_ids'])): ?><div class="text-danger small"><?= implode(', ', $errors['cita_ids']) ?></div><?php endif; ?>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Subtotal</label>
        <input type="text" readonly class="form-control" id="subtotal" value="0.00">
    </div>
    <div class="col-md-4">
        <label class="form-label">Descuento</label>
        <input type="number" step="0.01" name="descuento" id="descuento" class="form-control" value="<?= htmlspecialchars($venta['descuento'] ?? '0') ?>">
        <?php if (!empty($errors['descuento'])): ?><div class="text-danger small"><?= implode(', ', $errors['descuento']) ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Total</label>
        <input type="text" readonly class="form-control" id="monto_total_visible" value="0.00">
        <input type="hidden" name="monto_total" id="monto_total" value="0">
    </div>
</div>
<input type="hidden" name="monto_pagado" id="monto_pagado" value="<?= htmlspecialchars($venta['monto_pagado'] ?? '0') ?>">
<input type="hidden" name="estado_pago" id="estado_pago" value="<?= htmlspecialchars($venta['estado_pago'] ?? 'pendiente') ?>">
<input type="hidden" name="cobrar" id="cobrar" value="0">
<div class="mt-4 d-flex gap-2">
    <button type="button" class="btn btn-primary" id="btn-guardar">Guardar</button>
    <a href="<?= url('/ventas') ?>" class="btn btn-link">Cancelar</a>
</div>

<div class="modal fade" id="modalCobro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar cobro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Total a cobrar: <strong id="total-modal">GS. 0.00</strong></p>
                <p class="text-muted">¿Desea registrar el cobro ahora?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="btn-guardar-sin-cobrar">Guardar sin cobrar</button>
                <button type="button" class="btn btn-success" id="btn-cobrar">Cobrar ahora</button>
            </div>
        </div>
    </div>
</div>