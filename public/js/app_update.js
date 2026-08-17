(function () {
    var bannerEl, textEl, btnEl;

    function poll() {
        fetch('/update-status', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(render)
            .catch(function () { /* sin internet o app aun cargando, se reintenta en el siguiente poll */ });
    }

    function render(data) {
        if (!bannerEl) {
            bannerEl = document.getElementById('updateBanner');
            textEl = document.getElementById('updateBannerText');
            btnEl = document.getElementById('updateBannerBtn');
        }
        if (!bannerEl) return;

        var state = data.state || 'none';

        if (state === 'available') {
            textEl.textContent = 'Hay una nueva actualización disponible' + (data.version ? ' (v' + data.version + ')' : '') + '. Descargando...';
            btnEl.style.display = 'none';
            bannerEl.style.display = 'flex';
        } else if (state === 'downloading') {
            textEl.textContent = 'Descargando actualización...' + (data.percent ? ' ' + data.percent + '%' : '');
            btnEl.style.display = 'none';
            bannerEl.style.display = 'flex';
        } else if (state === 'ready') {
            textEl.textContent = 'Actualización lista' + (data.version ? ' (v' + data.version + ')' : '') + '. Reinicia para instalarla.';
            btnEl.style.display = 'inline-block';
            bannerEl.style.display = 'flex';
        } else {
            bannerEl.style.display = 'none';
        }
    }

    window.installUpdate = function () {
        if (!confirm('El POS se va a cerrar para instalar la actualización. ¿Continuar?')) return;
        var form = document.getElementById('updateInstallForm');
        if (form) form.submit();
    };

    document.addEventListener('DOMContentLoaded', function () {
        poll();
        setInterval(poll, 20000);
    });
})();
