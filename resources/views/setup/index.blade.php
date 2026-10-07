<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración inicial — POSTCI</title>
    <link rel="stylesheet" href="{{ asset('css/tci-colors.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--tci-navy) 0%, var(--tci-blue) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 1.5rem;
        }

        .card {
            background: var(--tci-white);
            border-radius: 16px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.28);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--tci-deep), var(--tci-blue));
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            color: var(--tci-white);
        }

        .card-header img {
            width: 80px;
            height: auto;
            margin-bottom: 0.75rem;
            filter: brightness(0) invert(1);
            opacity: 0.95;
        }

        .card-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .card-header p {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Steps */
        .steps {
            display: flex;
            padding: 1rem 2rem 0;
            gap: 0;
            position: relative;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 1.6rem;
            left: calc(2rem + 1rem);
            right: calc(2rem + 1rem);
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        .step {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            position: relative;
            z-index: 1;
        }

        .step-dot {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: #e5e7eb;
            color: #9ca3af;
            font-size: 0.78rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .step.active .step-dot   { background: var(--tci-blue); color: var(--tci-white); }
        .step.done .step-dot     { background: #16a34a; color: var(--tci-white); }

        .step-label {
            font-size: 0.68rem;
            color: #9ca3af;
            font-weight: 500;
            text-align: center;
        }

        .step.active .step-label { color: var(--tci-blue); font-weight: 600; }
        .step.done .step-label   { color: #16a34a; }

        /* Body */
        .card-body { padding: 1.5rem 2rem 2rem; }

        .field { margin-bottom: 1rem; }

        .field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--tci-gray);
            margin-bottom: 0.35rem;
        }

        .field label span { font-weight: 400; color: #9ca3af; }

        .field input {
            width: 100%;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 0.6rem 0.8rem;
            font-size: 0.88rem;
            color: var(--tci-gray);
            outline: none;
            transition: border-color 0.2s;
        }

        .field input:focus { border-color: var(--tci-sky); box-shadow: 0 0 0 3px rgba(43,188,237,0.18); }

        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

        .alert {
            border-radius: 8px;
            padding: 0.65rem 0.9rem;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }

        .alert-error   { background: #fee2e2; color: #b91c1c; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #86efac; }

        .btn {
            width: 100%;
            padding: 0.7rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn:active { transform: scale(0.99); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-primary { background: var(--tci-blue); color: var(--tci-white); }
        .btn-primary:hover:not(:disabled) { background: var(--tci-deep); }
        .btn-success { background: #16a34a; color: var(--tci-white); margin-top: 0.25rem; }
        .btn-success:hover:not(:disabled) { background: #15803d; }

        .section-divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 1.25rem 0 1rem;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <img src="{{ asset('img/logo.png') }}" alt="POSTCI">
        <h1>Configuración inicial</h1>
        <p>Conecta el POS con la Matriz para empezar</p>
    </div>

    {{-- Steps --}}
    <div class="steps" id="stepsBar">
        <div class="step active" id="stepDot1">
            <div class="step-dot">1</div>
            <span class="step-label">Token</span>
        </div>
        <div class="step" id="stepDot2">
            <div class="step-dot">2</div>
            <span class="step-label">Empresa</span>
        </div>
    </div>

    <div class="card-body">

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        {{-- ── PASO 1: Token ─────────────────────── --}}
        <div id="step1">
            <div class="field">
                <label>Token de Matriz</label>
                <input id="tokenInput" type="password" placeholder="Pega el token aquí" autofocus>
                <div id="tokenError" class="alert alert-error" style="display:none; margin-top:0.5rem;"></div>
            </div>
            <button class="btn btn-primary" id="btnVerify" onclick="verifyToken()">
                Verificar conexión
            </button>
        </div>

        {{-- ── PASO 2: Datos empresa ──────────────── --}}
        <div id="step2" style="display:none;">
            <div class="alert alert-success">
                ✓ Conexión exitosa. Revisa y edita los datos si es necesario.
            </div>

            <form id="setupForm" action="{{ route('setup.complete') }}" method="POST">
                @csrf
                <input type="hidden" id="tokenHidden"  name="token">
                <input type="hidden" id="f_branch_id"  name="branch_id">

                <div class="field">
                    <label>Nombre comercial</label>
                    <input type="text" name="name" id="f_name">
                </div>
                <div class="field">
                    <label>Razón social</label>
                    <input type="text" name="razon_social" id="f_razon_social">
                </div>

                <div class="field-row">
                    <div class="field" style="margin-bottom:0;">
                        <label>RFC</label>
                        <input type="text" name="rfc" id="f_rfc">
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label>Régimen fiscal</label>
                        <input type="text" name="regimen_fiscal" id="f_regimen_fiscal" placeholder="601">
                    </div>
                </div>

                <hr class="section-divider">

                <div class="field-row">
                    <div class="field" style="margin-bottom:0;">
                        <label>Código postal</label>
                        <input type="text" name="codigo_postal" id="f_codigo_postal">
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label>Vigencia</label>
                        <input type="date" name="vigencia" id="f_vigencia">
                    </div>
                </div>

                <hr class="section-divider">

                <div class="field">
                    <label>Dirección</label>
                    <input type="text" name="address" id="f_address">
                </div>
                <div class="field">
                    <label>URL del logo <span>(opcional)</span></label>
                    <input type="text" name="path_logo" id="f_path_logo" placeholder="https://...">
                </div>

                <button type="submit" class="btn btn-success">
                    Guardar y sincronizar catálogo →
                </button>
            </form>
        </div>

    </div>
</div>

<script>
function verifyToken() {
    var token = document.getElementById('tokenInput').value.trim();
    var errEl = document.getElementById('tokenError');
    var btn   = document.getElementById('btnVerify');

    errEl.style.display = 'none';
    if (!token) {
        errEl.textContent = 'Ingresa el token.';
        errEl.style.display = 'block';
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Verificando...';

    fetch('{{ route("setup.verifyToken") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ token: token })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            errEl.textContent    = data.message || 'Error desconocido.';
            errEl.style.display  = 'block';
            btn.disabled         = false;
            btn.textContent      = 'Verificar conexión';
            return;
        }

        var s = data.sucursal;
        document.getElementById('tokenHidden').value      = token;
        document.getElementById('f_branch_id').value      = s.id             || '';
        document.getElementById('f_name').value           = s.name           || '';
        document.getElementById('f_razon_social').value   = s.razon_social   || '';
        document.getElementById('f_rfc').value            = s.rfc            || '';
        document.getElementById('f_regimen_fiscal').value = s.regimen_fiscal  || '';
        document.getElementById('f_codigo_postal').value  = s.codigo_postal  || '';
        document.getElementById('f_address').value        = s.address        || '';
        document.getElementById('f_vigencia').value       = s.vigencia       || '';
        document.getElementById('f_path_logo').value      = s.path_logo      || '';

        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';

        // Actualizar stepper
        document.getElementById('stepDot1').className = 'step done';
        document.getElementById('stepDot1').querySelector('.step-dot').textContent = '✓';
        document.getElementById('stepDot2').className = 'step active';
    })
    .catch(function() {
        errEl.textContent   = 'Error de red. Verifica tu conexión.';
        errEl.style.display = 'block';
        btn.disabled        = false;
        btn.textContent     = 'Verificar conexión';
    });
}

document.getElementById('tokenInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); verifyToken(); }
});
</script>
</body>
</html>
