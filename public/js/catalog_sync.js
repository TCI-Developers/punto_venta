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

    window.syncCatalogNow = function () {
        if (!btnEl) return;
        btnEl.disabled = true;
        btnEl.textContent = 'Sincronizando...';

        fetch('/catalogo-matriz-sync-ajax', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            }
        })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (result.success) {
                    textEl.textContent = 'Catálogo sincronizado con éxito.';
                    setTimeout(function () { bannerEl.style.display = 'none'; }, 4000);
                } else {
                    btnEl.disabled = false;
                    btnEl.textContent = 'Reintentar';
                    textEl.textContent = result.message || 'No se pudo sincronizar el catálogo.';
                }
            })
            .catch(function () {
                btnEl.disabled = false;
                btnEl.textContent = 'Reintentar';
                textEl.textContent = 'No se pudo sincronizar el catálogo.';
            });
    };

    document.addEventListener('DOMContentLoaded', function () {
        poll();
        setInterval(poll, 15 * 60 * 1000); // cada 15 min -- si implica una llamada real a la Matriz
    });
})();
