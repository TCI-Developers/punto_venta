<x-guest-layout>
    <x-slot name="logo">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="mx-auto" style="width: 10rem;">
    </x-slot>

    <div id="syncStatus" style="text-align:center; padding:1rem 0;">
        <div id="spinner" style="width:3rem; height:3rem; border:0.35rem solid #e5e7eb; border-top-color:#2563eb; border-radius:50%; animation:spin 0.8s linear infinite; margin:0 auto 1rem;"></div>
        <p id="syncMsg" style="font-size:0.95rem; font-weight:600; margin:0 0 0.3rem;">Sincronizando catálogo...</p>
        <small id="syncDetail" style="color:#6b7280;">Esto puede tardar unos segundos.</small>
    </div>

    <div id="syncError" style="display:none; background:#fee2e2; color:#b91c1c; border-radius:6px; padding:0.7rem 0.9rem; font-size:0.82rem; margin-top:0.5rem; text-align:center;"></div>

    <div id="retryArea" style="display:none; text-align:center; margin-top:1rem;">
        <button onclick="runSync()"
            style="background:#2563eb; color:#fff; border:none; border-radius:6px; padding:0.55rem 1.2rem; font-size:0.88rem; font-weight:600; cursor:pointer;">
            Reintentar
        </button>
        <a href="{{ route('admin.index') }}" style="display:block; margin-top:0.6rem; font-size:0.8rem; color:#6b7280;">
            Saltar sincronización y entrar
        </a>
    </div>

    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

    <script>
    function runSync() {
        document.getElementById('spinner').style.display = 'block';
        document.getElementById('syncMsg').textContent = 'Sincronizando catálogo...';
        document.getElementById('syncDetail').textContent = 'Esto puede tardar unos segundos.';
        document.getElementById('syncError').style.display = 'none';
        document.getElementById('retryArea').style.display = 'none';

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("catalogoMatriz.syncAjax") }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                showError(data.message || 'No se pudo completar la sincronización.');
                return;
            }
            document.getElementById('syncMsg').textContent = '¡Listo!';
            document.getElementById('syncDetail').textContent = 'Redirigiendo...';
            document.getElementById('spinner').style.borderTopColor = '#16a34a';
            setTimeout(function() { window.location.href = '{{ route("admin.index") }}'; }, 800);
        })
        .catch(function() {
            showError('Error de red. Verifica tu conexión.');
        });
    }

    function showError(msg) {
        document.getElementById('spinner').style.display = 'none';
        document.getElementById('syncMsg').textContent = 'No se pudo sincronizar';
        document.getElementById('syncDetail').textContent = '';
        document.getElementById('syncError').textContent = msg;
        document.getElementById('syncError').style.display = 'block';
        document.getElementById('retryArea').style.display = 'block';
    }

    window.addEventListener('load', runSync);
    </script>
</x-guest-layout>
