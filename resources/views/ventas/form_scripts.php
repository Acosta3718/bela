<script>
    const citaServicios = <?= json_encode($serviciosPorCita ?? []) ?>;
    const ticketUtils = window.ticketPrinter || {};
    const toMoney = ticketUtils.toMoney || function (number) {
        return Number(number || 0).toLocaleString('es-PY', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    function actualizarTotales() {
        const checkboxes = document.querySelectorAll('.cita-option');
        let subtotal = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) {
                subtotal += parseFloat(cb.dataset.total || 0);
            }
        });
        const descuento = Math.round(parseFloat(document.getElementById('descuento').value || 0));
        const subtotalRedondeado = Math.round(subtotal);
        const total = Math.max(0, subtotalRedondeado - descuento);
        document.getElementById('subtotal').value = toMoney(subtotalRedondeado);
        document.getElementById('monto_total_visible').value = toMoney(total);
        document.getElementById('monto_total').value = total;
        document.getElementById('total-modal').innerText = 'Gs ' + toMoney(total);
        return { subtotal: subtotalRedondeado, total, descuento };
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
        actualizarTotales();
        document.querySelectorAll('.cita-option').forEach(cb => cb.addEventListener('change', actualizarTotales));
        document.getElementById('descuento').addEventListener('input', actualizarTotales);

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