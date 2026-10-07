<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importando datos — POSTCI</title>
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
            max-width: 520px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--tci-deep), var(--tci-blue));
            padding: 1.5rem 2rem;
            text-align: center;
            color: var(--tci-white);
        }

        .card-header img    { width: 56px; height: auto; margin-bottom: 0.5rem; filter: brightness(0) invert(1); opacity: .9; }
        .card-header h1     { font-size: 1.1rem; font-weight: 700; margin-bottom: .15rem; }
        .card-header p      { font-size: 0.75rem; opacity: .8; }

        /* Steps */
        .steps {
            display: flex;
            padding: .85rem 2rem 0;
            position: relative;
        }
        .steps::before {
            content: '';
            position: absolute;
            top: 1.425rem;
            left: calc(2rem + 1rem);
            right: calc(2rem + 1rem);
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }
        .step { flex: 1; display: flex; flex-direction: column; align-items: center; gap: .3rem; position: relative; z-index: 1; }
        .step-dot {
            width: 1.75rem; height: 1.75rem; border-radius: 50%;
            background: #e5e7eb; color: #9ca3af;
            font-size: .72rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .step.done   .step-dot { background: #16a34a; color: var(--tci-white); }
        .step.active .step-dot { background: var(--tci-blue); color: var(--tci-white); }
        .step-label { font-size: .65rem; color: #9ca3af; font-weight: 500; }
        .step.done   .step-label { color: #16a34a; }
        .step.active .step-label { color: var(--tci-blue); font-weight: 600; }

        /* Body */
        .card-body { padding: 1.25rem 1.75rem 1.75rem; }

        .task-list { display: flex; flex-direction: column; gap: .55rem; }

        .task {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .7rem .85rem;
            border-radius: 10px;
            background: var(--tci-light);
            border: 1.5px solid #e5e7eb;
            transition: border-color .25s, background .25s;
        }
        .task.running { border-color: var(--tci-sky); background: #f0f8ff; }
        .task.done    { border-color: #86efac; background: #f0fdf4; }
        .task.error   { border-color: #fca5a5; background: #fff1f2; }
        .task.skipped { border-color: #d1d5db; background: var(--tci-light); }

        .task-icon {
            flex-shrink: 0;
            width: 1.6rem; height: 1.6rem;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .8rem; font-weight: 700;
            background: #e5e7eb; color: #9ca3af;
        }
        .task.running .task-icon { background: rgba(43,188,237,.15); }
        .task.done    .task-icon { background: #dcfce7; color: #16a34a; }
        .task.error   .task-icon { background: #fee2e2; color: #dc2626; }
        .task.skipped .task-icon { background: var(--tci-light); color: #9ca3af; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            width: 1rem; height: 1rem;
            border: .18rem solid rgba(43,188,237,.35);
            border-top-color: var(--tci-sky);
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        .task-body    { flex: 1; min-width: 0; }
        .task-name    { font-size: .82rem; font-weight: 600; color: var(--tci-gray); }
        .task-msg     { font-size: .73rem; color: #6b7280; margin-top: .15rem; }
        .task.running .task-msg { color: var(--tci-blue); }
        .task.done    .task-msg { color: #15803d; }
        .task.error   .task-msg { color: #dc2626; }
        .task.skipped .task-msg { color: #9ca3af; }

        .task-actions { display: flex; gap: .4rem; margin-top: .35rem; }
        .task-btn {
            font-size: .7rem; font-weight: 600; border: none; border-radius: 5px;
            padding: .2rem .55rem; cursor: pointer;
        }
        .task-btn-retry { background: var(--tci-blue); color: var(--tci-white); }
        .task-btn-skip  { background: var(--tci-light); color: var(--tci-gray); }

        .divider { border: none; border-top: 1px solid var(--tci-light); margin: 1rem 0; }

        .btn-enter {
            width: 100%; padding: .7rem; border: none; border-radius: 8px;
            background: #16a34a; color: var(--tci-white);
            font-size: .9rem; font-weight: 700;
            cursor: pointer; display: none;
            transition: background .2s;
        }
        .btn-enter:hover { background: #15803d; }

        .progress-bar-wrap { height: 4px; background: #e5e7eb; border-radius: 2px; margin-bottom: 1rem; }
        .progress-bar      { height: 100%; border-radius: 2px; background: var(--tci-blue); width: 0; transition: width .4s; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <img src="{{ asset('img/logo.png') }}" alt="POSTCI">
        <h1>Configuración inicial</h1>
        <p id="headerMsg">Importando datos del sistema...</p>
    </div>

    <div class="steps">
        <div class="step done">
            <div class="step-dot">✓</div>
            <span class="step-label">Token</span>
        </div>
        <div class="step done">
            <div class="step-dot">✓</div>
            <span class="step-label">Empresa</span>
        </div>
        <div class="step active" id="stepDot3">
            <div class="step-dot">3</div>
            <span class="step-label">Datos</span>
        </div>
    </div>

    <div class="card-body">
        <div class="progress-bar-wrap">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <div class="task-list" id="taskList">

            <div class="task" id="task-catalogo">
                <div class="task-icon">📦</div>
                <div class="task-body">
                    <div class="task-name">Catálogo (marcas + productos + usuarios)</div>
                    <div class="task-msg" id="msg-catalogo">En espera...</div>
                </div>
            </div>

            <div class="task" id="task-metodos">
                <div class="task-icon">💳</div>
                <div class="task-body">
                    <div class="task-name">Métodos de pago</div>
                    <div class="task-msg" id="msg-metodos">En espera...</div>
                </div>
            </div>

            <div class="task" id="task-unidades">
                <div class="task-icon">📐</div>
                <div class="task-body">
                    <div class="task-name">Unidades SAT</div>
                    <div class="task-msg" id="msg-unidades">En espera...</div>
                </div>
            </div>

            <div class="task" id="task-choferes">
                <div class="task-icon">🚗</div>
                <div class="task-body">
                    <div class="task-name">Choferes</div>
                    <div class="task-msg" id="msg-choferes">En espera...</div>
                </div>
            </div>

            <div class="task" id="task-cliente">
                <div class="task-icon">👤</div>
                <div class="task-body">
                    <div class="task-name">Cliente general</div>
                    <div class="task-msg" id="msg-cliente">En espera...</div>
                </div>
            </div>

            <div class="task" id="task-sistema">
                <div class="task-icon">⚙️</div>
                <div class="task-body">
                    <div class="task-name">Configuración del sistema</div>
                    <div class="task-msg" id="msg-sistema">En espera...</div>
                </div>
            </div>

        </div>

        <hr class="divider">
        <button class="btn-enter" id="btnEnter" onclick="window.location.href='{{ route('admin.index') }}'">
            Entrar al sistema →
        </button>
    </div>
</div>

<script>
var CSRF = '{{ csrf_token() }}';
var STEPS = [
    {
        id: 'catalogo',
        label: 'Catálogo',
        url: '{{ route("catalogoMatriz.syncAjax") }}',
        skipable: false,
        onSuccess: function(data) {
            var c = data.counts || {};
            var parts = [];
            if (c.lineas)    parts.push(c.lineas    + ' marcas');
            if (c.productos) parts.push(c.productos + ' productos');
            if (c.usuarios)  parts.push(c.usuarios  + ' usuarios');
            if (c.descuentos) parts.push(c.descuentos + ' descuentos');
            return parts.length ? parts.join(', ') + ' importados.' : 'Catálogo sincronizado.';
        }
    },
    {
        id: 'metodos',
        label: 'Métodos de pago',
        url: '{{ route("setup.syncPaymentMethods") }}',
        skipable: true
    },
    {
        id: 'unidades',
        label: 'Unidades SAT',
        url: '{{ route("setup.syncUnidades") }}',
        skipable: true
    },
    {
        id: 'choferes',
        label: 'Choferes',
        url: '{{ route("setup.syncDrivers") }}',
        skipable: true
    },
    {
        id: 'cliente',
        label: 'Cliente general',
        url: '{{ route("setup.createClienteGeneral") }}',
        skipable: true
    },
    {
        id: 'sistema',
        label: 'Configuración del sistema',
        url: '{{ route("setup.configureSystem") }}',
        skipable: false
    }
];

var current = 0;

function setStatus(id, state, msg) {
    var task = document.getElementById('task-' + id);
    var msgEl = document.getElementById('msg-' + id);
    task.className = 'task ' + state;

    var icon = task.querySelector('.task-icon');

    // Limpiar actions anteriores
    var prev = task.querySelector('.task-actions');
    if (prev) prev.remove();

    if (state === 'running') {
        icon.innerHTML = '<div class="spinner"></div>';
    } else if (state === 'done') {
        icon.textContent = '✓';
    } else if (state === 'error') {
        icon.textContent = '✗';
    } else if (state === 'skipped') {
        icon.textContent = '—';
    } else {
        // pending — emoji original
    }

    if (msg) msgEl.textContent = msg;
}

function addActions(id, stepIndex) {
    var task = document.getElementById('task-' + id);
    var div = document.createElement('div');
    div.className = 'task-actions';
    div.innerHTML =
        '<button class="task-btn task-btn-retry" onclick="retryStep(' + stepIndex + ')">Reintentar</button>' +
        '<button class="task-btn task-btn-skip"  onclick="skipStep(' + stepIndex + ')">Omitir</button>';
    task.querySelector('.task-body').appendChild(div);
}

function updateProgress() {
    var pct = Math.round((current / STEPS.length) * 100);
    document.getElementById('progressBar').style.width = pct + '%';
}

function runStep(index) {
    if (index >= STEPS.length) {
        finish();
        return;
    }

    var step = STEPS[index];
    setStatus(step.id, 'running', 'Importando...');
    updateProgress();

    var fd = new FormData();
    fd.append('_token', CSRF);

    fetch(step.url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            setStatus(step.id, 'error', data.message || 'Error desconocido.');
            addActions(step.id, index);
            return; // pausar, esperar retry/skip
        }
        var msg = data.message || 'Completado.';
        if (step.onSuccess) msg = step.onSuccess(data);
        setStatus(step.id, 'done', msg);
        current = index + 1;
        updateProgress();
        setTimeout(function() { runStep(current); }, 300);
    })
    .catch(function() {
        setStatus(step.id, 'error', 'Error de red.');
        addActions(step.id, index);
    });
}

function retryStep(index) {
    var step = STEPS[index];
    var task = document.getElementById('task-' + step.id);
    var prev = task.querySelector('.task-actions');
    if (prev) prev.remove();
    runStep(index);
}

function skipStep(index) {
    var step = STEPS[index];
    var task = document.getElementById('task-' + step.id);
    var prev = task.querySelector('.task-actions');
    if (prev) prev.remove();
    setStatus(step.id, 'skipped', 'Omitido — configura manualmente después.');
    current = index + 1;
    updateProgress();
    setTimeout(function() { runStep(current); }, 200);
}

function finish() {
    document.getElementById('progressBar').style.width = '100%';
    document.getElementById('progressBar').style.background = '#16a34a';
    document.getElementById('headerMsg').textContent = '¡Todo listo! El sistema está configurado.';
    document.getElementById('stepDot3').className = 'step done';
    document.getElementById('stepDot3').querySelector('.step-dot').textContent = '✓';
    document.getElementById('stepDot3').querySelector('.step-label').textContent = 'Listo';
    document.getElementById('btnEnter').style.display = 'block';
}

window.addEventListener('load', function() { runStep(0); });
</script>
</body>
</html>
