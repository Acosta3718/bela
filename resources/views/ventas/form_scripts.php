<script>
    const citaServicios = <?= json_encode($serviciosPorCita ?? []) ?>;
    const ticketUtils = window.ticketPrinter || {};
    const toMoney = ticketUtils.toMoney || function (number) {
        return Number(number || 0).toLocaleString('es-PY', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    let subtotalManual = false;

    function subtotalSeleccionado() {
        const checkboxes = document.querySelectorAll('.cita-option');
        let subtotal = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) {
                subtotal += parseFloat(cb.dataset.total || 0);
            }
        });
        return Math.round(subtotal);
    }

    function actualizarTotales(desdeSeleccion = false) {
        const descuento = Math.round(parseFloat(document.getElementById('descuento').value || 0));
        const subtotalInput = document.getElementById('subtotal');
        const subtotalCalculado = subtotalSeleccionado();
        let subtotal = subtotalCalculado;

        if (subtotalManual) {
            subtotal = Math.max(0, Math.round(parseFloat(subtotalInput.value || 0)));
        } else {
            subtotal = subtotalCalculado;
            subtotalInput.value = subtotal;
        }

        const total = Math.max(0, subtotal - descuento);
        document.getElementById('monto_total_visible').value = toMoney(total);
        document.getElementById('monto_total').value = total;
        document.getElementById('total-modal').innerText = 'Gs ' + toMoney(total);
        return { subtotal, total, descuento };
    }

    function datosTicketSeleccionado() {
        const seleccion = Array.from(document.querySelectorAll('.cita-option:checked'));
        if (!seleccion.length) return null;
        const { descuento } = actualizarTotales();
        const cliente = seleccion[0].dataset.cliente || '';
        const servicios = [];
        seleccion.forEach(cb => {
            (citaServicios[cb.value] || []).forEach(serv => servicios.push(serv));
        });
        const citas = seleccion.map(cb => ({
            id: cb.value,
            fecha: cb.dataset.fecha,
            hora: cb.dataset.hora,
        }));
        return { cliente, citas, servicios, descuento };
    }

    document.addEventListener('DOMContentLoaded', () => {
        const subtotalInput = document.getElementById('subtotal');
        const subtotalInicial = Math.round(parseFloat(subtotalInput.value || 0));
        const subtotalCalculado = subtotalSeleccionado();
        subtotalManual = subtotalInicial > 0 && subtotalInicial !== subtotalCalculado && subtotalCalculado > 0;

        if (!subtotalManual) {
            subtotalInput.value = subtotalCalculado || subtotalInicial || 0;
        }

        actualizarTotales();
        document.querySelectorAll('.cita-option').forEach(cb => cb.addEventListener('change', () => {
            subtotalManual = false;
            actualizarTotales(true);
        }));
        document.getElementById('descuento').addEventListener('input', () => actualizarTotales(false));
        subtotalInput.addEventListener('input', () => {
            subtotalManual = true;
            actualizarTotales(false);
        });
        subtotalInput.addEventListener('blur', () => {
            if (subtotalInput.value === '') {
                subtotalManual = false;
                actualizarTotales(true);
            }
        });

        const modalElement = document.getElementById('modalCobro');
        const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
        const ventaForm = document.getElementById('venta-form');
        const cuentaSelect = document.getElementById('cuenta_id');

        function submitVenta() {
            if (ventaForm.requestSubmit) {
                ventaForm.requestSubmit();
            } else {
                ventaForm.submit();
            }
        }

        document.getElementById('btn-guardar').addEventListener('click', () => {
            const seleccion = document.querySelectorAll('.cita-option:checked');
            if (!seleccion.length) {
                alert('Seleccione al menos una cita');
                return;
            }
            actualizarTotales();
            if (modal) {
                modal.show();
            } else {
                document.getElementById('cobrar').value = 0;
                ventaForm.dataset.cobrar = '0';
                submitVenta();
            }
        });

        document.getElementById('btn-guardar-sin-cobrar').addEventListener('click', () => {
            document.getElementById('cobrar').value = 0;
            document.getElementById('estado_pago').value = 'pendiente';
            ventaForm.dataset.cobrar = '0';
            if (cuentaSelect) cuentaSelect.value = '';
            submitVenta();
        });

        document.getElementById('btn-cobrar').addEventListener('click', () => {
            if (cuentaSelect && !cuentaSelect.value) {
                alert('Seleccione la cuenta donde se registrará el cobro.');
                return;
            }
            document.getElementById('cobrar').value = 1;
            document.getElementById('estado_pago').value = 'pagado';
            const total = actualizarTotales().total;
            document.getElementById('monto_pagado').value = total;
            ventaForm.dataset.cobrar = '1';
            submitVenta();
        });

        ventaForm.addEventListener('submit', () => {
            if (ventaForm.dataset.enviando === '1') return;

            const quiereCobrar = ventaForm.dataset.cobrar === '1' || document.getElementById('cobrar').value === '1';
            if (quiereCobrar && cuentaSelect && !cuentaSelect.value) {
                event.preventDefault();
                alert('Seleccione la cuenta donde se registrará el cobro.');
                return;
            }
            if (quiereCobrar && window.ticketPrinter) {
                const ticketData = datosTicketSeleccionado();
                if (ticketData) {
                    window.ticketPrinter.printTicket(ticketData);
                }
            }
            ventaForm.dataset.enviando = '1';
        });
    });
</script>