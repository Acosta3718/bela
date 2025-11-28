<template id="ticket-template">
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket</title>
    <style>
        body { font-family: monospace; font-size: 12px; }
        h4 { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; }
        hr { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
    </style>
</head>
<body>
    <div style="width:280px;">
        <div style="text-align:center;">
            <h4>Bela - Ticket de pago</h4>
            <div>{{FECHA}}</div>
        </div>
        <hr>
        <div><strong>Cliente:</strong> {{CLIENTE}}</div>
        <div><strong>Citas:</strong><br>{{CITAS}}</div>
        <hr>
        <table>
            {{DETALLE_SERVICIOS}}
        </table>
        <hr>
        <div>Subtotal: {{SUBTOTAL}}</div>
        <div>Descuento: {{DESCUENTO}}</div>
        <div><strong>Total: GS {{TOTAL}}</strong></div>
        <div style="margin-top:6px;">Monto en letras: Guaranies {{LETRAS}}</div>
    </div>
</body>
</html>
</template>
<script>
    (function () {
        const template = document.getElementById('ticket-template');

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

        function buildTicket(data) {
            if (!template) return null;
            const copia = template.innerHTML || (template.content ? template.content.innerHTML : '');
            if (!copia) return null;
            const servicios = Array.isArray(data.servicios) ? data.servicios : [];
            const citas = Array.isArray(data.citas) ? data.citas : [];
            const descuento = parseFloat(data.descuento || 0);
            const fecha = data.fechaImpresion ? new Date(data.fechaImpresion) : new Date();

            let subtotal = 0;
            const filas = servicios.map(serv => {
                subtotal += parseFloat(serv.precio_base || 0);
                return `<tr><td>${serv.nombre}</td><td style="text-align:right;">$${toMoney(serv.precio_base)}</td></tr>`;
            });

            const totalCalculado = Math.max(0, subtotal - descuento);
            const resumenCitas = citas.map(c => `#${c.id} - ${c.fecha || ''} ${(c.hora || '').substring(0, 5)}`).join('<br>');
            const montoEnLetras = numeroALetras(totalCalculado);

            return copia
                .replace('{{FECHA}}', fecha.toLocaleString())
                .replace('{{CLIENTE}}', data.cliente || 'N/A')
                .replace('{{CITAS}}', resumenCitas || 'N/A')
                .replace('{{DETALLE_SERVICIOS}}', filas.join(''))
                .replace('{{SUBTOTAL}}', toMoney(subtotal))
                .replace('{{DESCUENTO}}', toMoney(descuento))
                .replace('{{TOTAL}}', toMoney(totalCalculado))
                .replace('{{LETRAS}}', montoEnLetras);
        }

        function printTicket(data) {
            const html = buildTicket(data);
            if (!html) return null;
            const ventana = window.open('', 'PRINT', 'height=600,width=400');
            if (!ventana) return null;

            const triggerPrint = () => {
                ventana.focus();
                ventana.print();
                setTimeout(() => ventana.close(), 50);
            };

            ventana.document.open();
            ventana.document.write(html);
            ventana.document.close();

            if (ventana.document.readyState === 'complete') {
                setTimeout(triggerPrint, 50);
            } else {
                ventana.document.addEventListener('DOMContentLoaded', triggerPrint);
            }

            return ventana;
        }

        window.ticketPrinter = {
            toMoney,
            numeroALetras,
            buildTicket,
            printTicket,
        };
    })();
</script>