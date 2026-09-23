(function () {
    var bannerEl, textEl, btnEl;

    function csrfToken() {
        var input = document.querySelector('#catalogSyncCsrfForm input[name="_token"]');
        return input ? input.value : '';
    }

    function poll() {
        fetch('/catalogo-matriz-status', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(render)
            .catch(function () { /* sin internet o sin permiso, se reintenta en el siguiente poll */ });
    }

    function render(data) {
        if (!bannerEl) {
            bannerEl = document.getElementById('catalogBanner');
            textEl = document.getElementById('catalogBannerText');
            btnEl = document.getElementById('catalogBannerBtn');
        }
        if (!bannerEl) return;

        if (data.pending) {
            textEl.textContent = 'Hay cambios en el catálogo disponibles (' + data.total + '). Sincronizar para aplicarlos.';
            bannerEl.style.display = 'flex';
        } else {
            bannerEl.style.display = 'none';
        }
    }

    function doSync(skip, onSuccess, onError) {
        var body = new FormData();
        (skip || []).forEach(function (s) { body.append('skip[]', s); });
        fetch('/catalogo-matriz-sync-ajax', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (result.success) { onSuccess(result); } else { onError(result.message || 'No se pudo sincronizar el catálogo.'); }
            })
            .catch(function () { onError('No se pudo sincronizar el catálogo.'); });
    }

    // Sync desde el banner (requiere que el banner esté visible)
    window.syncCatalogNow = function () {
        if (!btnEl) return;
        btnEl.disabled = true;
        btnEl.textContent = 'Sincronizando...';
        doSync(
            [],
            function () {
                textEl.textContent = 'Catálogo sincronizado con éxito.';
                setTimeout(function () { bannerEl.style.display = 'none'; }, 4000);
            },
            function (msg) {
                btnEl.disabled = false;
                btnEl.textContent = 'Reintentar';
                textEl.textContent = msg;
            }
        );
    };

    // Sync desde cualquier botón externo (ej. módulo de productos o clientes)
    // skip: array de secciones a omitir, ej. ['clientes']
    window.syncCatalogManual = function (triggerBtn, skip) {
        if (triggerBtn) {
            triggerBtn.disabled = true;
            triggerBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        }
        doSync(
            skip || [],
            function () {
                if (triggerBtn) {
                    triggerBtn.disabled = false;
                    triggerBtn.innerHTML = '<i class="fa fa-refresh"></i>';
                }
                poll();
            },
            function () {
                if (triggerBtn) {
                    triggerBtn.disabled = false;
                    triggerBtn.innerHTML = '<i class="fa fa-refresh"></i>';
                }
            }
        );
    };

    document.addEventListener('DOMContentLoaded', function () {
        poll();
        setInterval(poll, 15 * 60 * 1000); // cada 15 min -- si implica una llamada real a la Matriz
    });
})();
