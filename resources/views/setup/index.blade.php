<x-guest-layout>
    <x-slot name="logo">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="mx-auto" style="width: 10rem;">
    </x-slot>

    <div style="text-align:center; margin-bottom:1.2rem;">
        <h2 style="font-size:1.15rem; font-weight:700; margin:0 0 0.25rem;">Configuración inicial</h2>
        <p style="font-size:0.8rem; color:#6b7280; margin:0;">Ingresa el token de Matriz para conectar el POS.</p>
    </div>

    {{-- Mensajes de error de sesión --}}
    @if(session('error'))
        <div id="sessionError" style="background:#fee2e2; color:#b91c1c; border-radius:6px; padding:0.6rem 0.85rem; font-size:0.82rem; margin-bottom:1rem;">
            {{ session('error') }}
        </div>
    @endif

    {{-- PASO 1: Token --}}
    <div id="step1">
        <div style="margin-bottom:0.85rem;">
            <label style="font-size:0.82rem; font-weight:600; display:block; margin-bottom:0.3rem;">Token de Matriz</label>
            <input id="tokenInput" type="password" placeholder="Pega el token aquí"
                style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; padding:0.5rem 0.75rem; font-size:0.9rem;">
            <div id="tokenError" style="color:#b91c1c; font-size:0.78rem; margin-top:0.35rem; display:none;"></div>
        </div>
        <button id="btnVerify" onclick="verifyToken()"
            style="width:100%; background:#2563eb; color:#fff; border:none; border-radius:6px; padding:0.6rem; font-size:0.9rem; font-weight:600; cursor:pointer;">
            Verificar conexión
        </button>
    </div>

    {{-- PASO 2: Datos de empresa (oculto hasta verificar) --}}
    <div id="step2" style="display:none; margin-top:1.25rem;">
        <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:6px; padding:0.6rem 0.85rem; font-size:0.82rem; color:#166534; margin-bottom:1rem;">
            ✓ Conexión exitosa. Revisa y edita los datos si es necesario.
        </div>

        <form id="setupForm" action="{{ route('setup.complete') }}" method="POST">
            @csrf
            <input type="hidden" id="tokenHidden" name="token">
            <input type="hidden" id="f_branch_id" name="branch_id">

            <div style="display:grid; gap:0.7rem;">
                <div>
                    <label style="font-size:0.78rem; font-weight:600; display:block; margin-bottom:0.2rem;">Nombre comercial</label>
                    <input type="text" name="name" id="f_name"
                        style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; padding:0.45rem 0.65rem; font-size:0.85rem;">
                </div>
                <div>
                    <label style="font-size:0.78rem; font-weight:600; display:block; margin-bottom:0.2rem;">Razón social</label>
                    <input type="text" name="razon_social" id="f_razon_social"
                        style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; padding:0.45rem 0.65rem; font-size:0.85rem;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.7rem;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; display:block; margin-bottom:0.2rem;">RFC</label>
                        <input type="text" name="rfc" id="f_rfc"
                            style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; padding:0.45rem 0.65rem; font-size:0.85rem;">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; display:block; margin-bottom:0.2rem;">Régimen fiscal</label>
                        <input type="text" name="regimen_fiscal" id="f_regimen_fiscal" placeholder="601"
                            style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; padding:0.45rem 0.65rem; font-size:0.85rem;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.7rem;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; display:block; margin-bottom:0.2rem;">Código postal</label>
                        <input type="text" name="codigo_postal" id="f_codigo_postal"
                            style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; padding:0.45rem 0.65rem; font-size:0.85rem;">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; display:block; margin-bottom:0.2rem;">Vigencia</label>
                        <input type="date" name="vigencia" id="f_vigencia"
                            style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; padding:0.45rem 0.65rem; font-size:0.85rem;">
                    </div>
                </div>
                <div>
                    <label style="font-size:0.78rem; font-weight:600; display:block; margin-bottom:0.2rem;">Dirección</label>
                    <input type="text" name="address" id="f_address"
                        style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; padding:0.45rem 0.65rem; font-size:0.85rem;">
                </div>
                <div>
                    <label style="font-size:0.78rem; font-weight:600; display:block; margin-bottom:0.2rem;">URL del logo <span style="font-weight:400; color:#6b7280;">(opcional)</span></label>
                    <input type="text" name="path_logo" id="f_path_logo"
                        style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; padding:0.45rem 0.65rem; font-size:0.85rem;">
                </div>
            </div>

            <button type="submit"
                style="width:100%; margin-top:1rem; background:#16a34a; color:#fff; border:none; border-radius:6px; padding:0.6rem; font-size:0.9rem; font-weight:600; cursor:pointer;">
                Guardar y sincronizar catálogo →
            </button>
        </form>
    </div>

    <script>
    function verifyToken() {
        var token = document.getElementById('tokenInput').value.trim();
        var errEl = document.getElementById('tokenError');
        var btn = document.getElementById('btnVerify');

        errEl.style.display = 'none';
        if (!token) {
            errEl.textContent = 'Ingresa el token.';
            errEl.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Verificando...';

        fetch('{{ route("setup.verifyToken") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ token: token })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                errEl.textContent = data.message || 'Error desconocido.';
                errEl.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Verificar conexión';
                return;
            }
            // Rellenar campos con datos de sucursal
            var s = data.sucursal;
            document.getElementById('tokenHidden').value = token;
            document.getElementById('f_branch_id').value     = s.id            || '';
            document.getElementById('f_name').value          = s.name          || '';
            document.getElementById('f_razon_social').value  = s.razon_social  || '';
            document.getElementById('f_rfc').value           = s.rfc           || '';
            document.getElementById('f_regimen_fiscal').value = s.regimen_fiscal || '';
            document.getElementById('f_codigo_postal').value = s.codigo_postal || '';
            document.getElementById('f_address').value       = s.address       || '';
            document.getElementById('f_vigencia').value      = s.vigencia      || '';
            document.getElementById('f_path_logo').value     = s.path_logo     || '';

            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
        })
        .catch(function() {
            errEl.textContent = 'Error de red. Verifica tu conexión.';
            errEl.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Verificar conexión';
        });
    }

    // Permitir Enter en el campo token
    document.getElementById('tokenInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); verifyToken(); }
    });
    </script>
</x-guest-layout>
