<script>
    const citaServicios = <?= json_encode($serviciosPorCita ?? []) ?>;

    function toMoney(number) {
        return Number(number || 0).toFixed(2);
    }

    function numeroALetras(num) {
        const unidades = ['cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        const decenas = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
        const tens = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        const cientos = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

        function convertirMenor100(n) {
            if (n < 10) return unidades[n];
            if (n < 20) return decenas[n - 10];
            if (n === 20) return 'veinte';
            const t = Math.floor(n / 10);
            const u = n % 10;
            if (n < 30) return 'veinti' + (u === 0 ? '' : unidades[u]);
            return tens[t] + (u ? ' y ' + unidades[u] : '');
        }

        function convertirMenor1000(n) {
            if (n === 100) return 'cien';
            const c = Math.floor(n / 100);
            const resto = n % 100;
            const partes = [];
            if (c) partes.push(cientos[c]);
            if (resto || (!c && !resto)) partes.push(convertirMenor100(resto));
            return partes.filter(Boolean).join(' ');
        }

        function convertir(n) {
            if (n === 0) return 'cero';
            const millones = Math.floor(n / 1000000);
            const miles = Math.floor((n % 1000000) / 1000);
            const resto = n % 1000;
            const partes = [];
            if (millones) partes.push(millones === 1 ? 'un millón' : convertir(millones) + ' millones');
            if (miles) partes.push(miles === 1 ? 'mil' : convertirMenor1000(miles) + ' mil');
            if (resto) partes.push(convertirMenor1000(resto));
            return partes.join(' ');
        }

        return convertir(Math.floor(num));
    }

    function actualizarTotales() {
        const checkboxes = document.querySelectorAll('.cita-option');
        let subtotal = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) {
                subtotal += parseFloat(cb.dataset.total || 0);
            }
        });
        const descuento = parseFloat(document.getElementById('descuento').value || 0);
        const total = Math.max(0, subtotal - descuento);
        document.getElementById('subtotal').value = toMoney(subtotal);
        document.getElementById('monto_total_visible').value = toMoney(total);
        document.getElementById('monto_total').value = total;
        document.getElementById('total-modal').innerText = '$' + toMoney(total);
        return { subtotal, total, descuento };
    }

    function armarTicket() {
        const seleccion = Array.from(document.querySelectorAll('.cita-option:checked'));
        if (!seleccion.length) return null;
        const { total, descuento } = actualizarTotales();
        const fechaActual = new Date();
        const cliente = seleccion[0].dataset.cliente || '';

        const servicios = JSON.parse(JSON.stringify(citaServicios));
        const filas = [];
        let subtotal = 0;
        seleccion.forEach(cb => {
            (servicios[cb.value] || []).forEach(serv => {
                subtotal += parseFloat(serv.precio_base || 0);
                filas.push(`<tr><td>${serv.nombre}</td><td style="text-align:right;">$${toMoney(serv.precio_base)}</td></tr>`);
            });
        });

        const totalCalculado = Math.max(0, subtotal - (descuento || 0));
        const resumenCitas = seleccion.map(cb => `#${cb.value} - ${cb.dataset.fecha} ${cb.dataset.hora}`).join('<br>');
        const montoEnLetras = numeroALetras(totalCalculado);

        return `<!doctype html>
<html><head><meta charset="utf-8"><title>Ticket</title>
<style>body{font-family:monospace;font-size:12px;} h4{margin:4px 0;} table{width:100%;border-collapse:collapse;} td{padding:2px 0;} hr{border:0;border-top:1px dashed #000;margin:6px 0;}</style>
</head><body>
<div style="width:280px;">
    <div style="text-align:center;">
        <h4>Bela - Ticket de pago</h4>
        <div>${fechaActual.toLocaleString()}</div>
    </div>
    <hr>
    <div><strong>Cliente:</strong> ${cliente || 'N/A'}</div>
    <div><strong>Citas:</strong><br>${resumenCitas}</div>
    <hr>
    <table>
        ${filas.join('')}
    </table>
    <hr>
    <div>Subtotal: $${toMoney(subtotal)}</div>
    <div>Descuento: $${toMoney(descuento || 0)}</div>
    <div><strong>Total: $${toMoney(totalCalculado)}</strong></div>
    <div style="margin-top:6px;">Monto en letras: ${montoEnLetras}</div>
</div>
</body></html>`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        actualizarTotales();
        document.querySelectorAll('.cita-option').forEach(cb => cb.addEventListener('change', actualizarTotales));
        document.getElementById('descuento').addEventListener('input', actualizarTotales);

        const modalElement = document.getElementById('modalCobro');
        const modal = modalElement ? new bootstrap.Modal(modalElement) : null;

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
                document.getElementById('venta-form').submit();
            }
        });

        document.getElementById('btn-guardar-sin-cobrar').addEventListener('click', () => {
            document.getElementById('cobrar').value = 0;
            document.getElementById('estado_pago').value = 'pendiente';
            document.getElementById('venta-form').submit();
        });

        document.getElementById('btn-cobrar').addEventListener('click', () => {
            document.getElementById('cobrar').value = 1;
            document.getElementById('estado_pago').value = 'pagado';
            const total = actualizarTotales().total;
            document.getElementById('monto_pagado').value = total;
            document.getElementById('venta-form').dataset.cobrar = '1';
            document.getElementById('venta-form').submit();
        });

        const ventaForm = document.getElementById('venta-form');
        ventaForm.addEventListener('submit', (event) => {
            if (ventaForm.dataset.enviando === '1') return;

            const quiereCobrar = ventaForm.dataset.cobrar === '1' || document.getElementById('cobrar').value === '1';
            if (quiereCobrar) {
                const ticketHtml = armarTicket();
                if (ticketHtml) {
                    const ventana = window.open('', 'PRINT', 'height=600,width=400');
                    ventana.document.write(ticketHtml);
                    ventana.document.close();
                    ventana.focus();
                    ventana.onload = () => {
                        ventana.print();
                        ventana.close();
                    };
                }
            }

            ventaForm.dataset.enviando = '1';
        }, { once: true });
    });
</script>