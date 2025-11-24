<div class="modal fade" id="modal-nuevo-cliente" tabindex="-1" aria-labelledby="modal-nuevo-cliente-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-nuevo-cliente-label">Nuevo cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="form-nuevo-cliente" action="<?= url('/clientes/inline') ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" required maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" maxlength="150">
                    </div>
                    <div class="text-danger small mb-2 d-none" data-inline-error></div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const init = () => {
        const form = document.getElementById('form-nuevo-cliente');
        const modalEl = document.getElementById('modal-nuevo-cliente');

        if (!form || !modalEl || !window.bootstrap || !bootstrap.Modal) {
            return;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const errorBox = form.querySelector('[data-inline-error]');

        const limpiarErrores = () => {
            if (errorBox) {
                errorBox.textContent = '';
                errorBox.classList.add('d-none');
            }
        };

        const mostrarErrores = (errores) => {
            if (!errorBox) {
                return;
            }
            const mensajes = [];
            if (errores && typeof errores === 'object') {
                Object.values(errores).forEach((lista) => {
                    if (Array.isArray(lista)) {
                        mensajes.push(...lista);
                    }
                });
            }
            errorBox.textContent = mensajes.length ? mensajes.join(' ') : 'No se pudo guardar el cliente.';
            errorBox.classList.remove('d-none');
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            limpiarErrores();

            const formData = new FormData(form);
            const body = new URLSearchParams();
            formData.forEach((value, key) => {
                body.append(key, value.toString());
            });

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        return Promise.reject(payload.errors || null);
                    }
                    return payload;
                })
                .then((cliente) => {
                    if (window.belaClienteSelector) {
                        window.belaClienteSelector.set(cliente);
                    }
                    form.reset();
                    modal.hide();
                })
                .catch((errores) => {
                    mostrarErrores(errores);
                });
        });
    };

    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }
})();
</script>