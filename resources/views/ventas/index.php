<?php $title = 'Ventas'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Ventas</h1>
    <a href="<?= url('/ventas/crear') ?>" class="btn btn-primary">Nueva</a>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Cita</th>
            <th>Monto total</th>
            <th>Descuento</th>
            <th>Pagado</th>
            <th>Estado</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ventas as $venta): ?>
        <tr>
            <td>#<?= htmlspecialchars($venta['cita_id']) ?></td>
            <td>$<?= number_format((float)$venta['monto_total'], 2) ?></td>
            <td>$<?= number_format((float)$venta['descuento'], 2) ?></td>
            <td>$<?= number_format((float)$venta['monto_pagado'], 2) ?></td>
            <td><?= htmlspecialchars($venta['estado_pago']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-secondary" href="<?= url('/ventas/editar?id=' . $venta['id']) ?>">Editar</a>
                <form action="<?= url('/ventas/eliminar?id=' . $venta['id']) ?>" method="post" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar venta?')">Eliminar</button>
                </form>
                <form
                    action="<?= url('/ventas/cobro?id=' . $venta['id']) ?>"
                    method="post"
                    class="d-inline form-cobro"
                    data-venta-id="<?= $venta['id'] ?>"
                    data-cita-id="<?= $venta['cita_id'] ?>"
                >
                    <input type="hidden" name="monto" value="<?= max(0, $venta['monto_total'] - $venta['monto_pagado']) ?>">
                    <button class="btn btn-sm btn-success">Marcar pagado</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<script>
    const ventasData = <?= json_encode($ventas ?? []) ?>;
    const citaServicios = <?= json_encode($serviciosPorCita ?? []) ?>;
    const citasInfo = <?= json_encode($citasInfo ?? []) ?>;

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

    function armarTicket(venta) {
        if (!venta) return null;
        const servicios = citaServicios[venta.cita_id] || [];
        const cita = citasInfo[venta.cita_id] || {};
        const fechaActual = new Date();

        let subtotal = 0;
        const filas = servicios.map(serv => {
            subtotal += parseFloat(serv.precio_base || 0);
            return `<tr><td>${serv.nombre}</td><td style="text-align:right;">$${toMoney(serv.precio_base)}</td></tr>`;
        });

        const descuento = parseFloat(venta.descuento || 0);
        const totalCalculado = Math.max(0, subtotal - descuento);
        const resumenCitas = `#${venta.cita_id} - ${cita.fecha || ''} ${(cita.hora_inicio || '').substring(0,5)}`;
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
    <div><strong>Cliente:</strong> ${cita.cliente || 'N/A'}</div>
    <div><strong>Citas:</strong><br>${resumenCitas}</div>
    <hr>
    <table>
        ${filas.join('')}
    </table>
    <hr>
    <div>Subtotal: $${toMoney(subtotal)}</div>
    <div>Descuento: $${toMoney(descuento)}</div>
    <div><strong>Total: $${toMoney(totalCalculado)}</strong></div>
    <div style="margin-top:6px;">Monto en letras: ${montoEnLetras}</div>
</div>
</body></html>`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const ventasMap = ventasData.reduce((acc, venta) => {
            acc[venta.id] = venta;
            return acc;
        }, {});

        document.querySelectorAll('.form-cobro').forEach(form => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.enviando === '1') return;
                event.preventDefault();
                const ventaId = form.dataset.ventaId;
                const venta = ventasMap[ventaId];
                const ticketHtml = armarTicket(venta);
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
                form.dataset.enviando = '1';
                form.submit();
            });
        });
    });
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>