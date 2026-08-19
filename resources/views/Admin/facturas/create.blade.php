<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Factura</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <style>
        .tabla-ventas tbody tr { cursor: pointer; }
        .tabla-ventas tbody tr.seleccionada { background-color: #d4edda !important; }
        #resumen { background: #f8f9fa; border-radius: 4px; }

        /* Toggle modo timbrado */
        .modo-timbrado-toggle { display:inline-flex; align-items:center; gap:10px; cursor:pointer; user-select:none; margin:0; padding:8px 14px; border-radius:10px; border:1.5px solid #dee2e6; background:#fff; transition:all .25s; }
        .modo-timbrado-toggle:hover { border-color:#adb5bd; }
        .modo-timbrado-toggle input { display:none; }
        .modo-track { position:relative; width:40px; height:22px; background:#28a745; border-radius:11px; transition:background .25s; flex-shrink:0; }
        .modo-track::after { content:''; position:absolute; top:3px; left:3px; width:16px; height:16px; background:#fff; border-radius:50%; transition:transform .25s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
        .modo-timbrado-toggle input:checked ~ .modo-track { background:#f0ad4e; }
        .modo-timbrado-toggle input:checked ~ .modo-track::after { transform:translateX(18px); }
        .modo-label { display:flex; flex-direction:column; line-height:1.2; }
        .modo-label #modoNombre { font-weight:600; font-size:.9rem; color:#155724; }
        .modo-label #modoDesc { font-size:.75rem; color:#6c757d; }
        .modo-timbrado-toggle.es-prueba { border-color:#f0ad4e; background:#fffbf0; }
        .modo-timbrado-toggle.es-prueba #modoNombre { color:#856404; }

        /* Botón sustituye */
        .btn-sustituye { display:inline-flex; align-items:center; gap:6px; border:none; background:transparent; color:#6c757d; font-size:.82rem; cursor:pointer; padding:4px 8px; border-radius:6px; transition:all .2s; border:1px dashed #ced4da; }
        .btn-sustituye:hover:not(:disabled) { background:#fff3cd; color:#856404; border-color:#f0ad4e; }
        .btn-sustituye.activo { background:#fff3cd; color:#856404; border-color:#f0ad4e; border-style:solid; font-weight:600; }
        .btn-sustituye:disabled { opacity:.4; cursor:not-allowed; }
    </style>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')

        <div class="card card-primary">
            <div class="form-group card-header with-border text-center">
                <h2>
                    <a href="{{ route('facturas.index') }}" class="btn btn-success float-left btn-sm"
                       data-toggle="tooltip" title="Regresar"><i class="fa fa-arrow-left"></i></a>
                    Nueva Factura
                </h2>
            </div>
            <div class="card-body">

                @if(!$empresa?->rfc || !$empresa?->regimen_fiscal || !$empresa?->codigo_postal)
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    Los datos fiscales de la empresa están incompletos (RFC, régimen fiscal o código postal).
                    <a href="{{ route('admin.empresa') }}">Completar datos</a>
                </div>
                @endif

                <form action="{{ route('facturas.store') }}" method="POST" id="formFactura">
                    @csrf

                    {{-- ── Datos del receptor ──────────────────────────────── --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light"><strong>Datos del receptor</strong></div>
                        <div class="card-body">

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="publiCoGeneral"
                                           name="publico_general" value="1"
                                           {{ old('publico_general') ? 'checked' : '' }}
                                           onchange="toggleReceptor(this.checked)">
                                    <label class="custom-control-label" for="publiCoGeneral">
                                        Facturar a <strong>Público en General</strong>
                                        (RFC: XAXX010101000 — sin datos del cliente)
                                    </label>
                                </div>
                            </div>

                            <div id="bloqueCliente" {{ old('publico_general') ? 'style=display:none' : '' }}>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Cliente*
                                            <select name="customer_id" id="customer_id"
                                                    class="form-control selectpicker show-tick"
                                                    data-live-search="true" data-size="8"
                                                    title="Selecciona un cliente">
                                                <option value=""></option>
                                                @foreach($customers as $c)
                                                <option value="{{ $c->id }}"
                                                    data-rfc="{{ $c->rfc }}"
                                                    data-regimen="{{ $c->regimen_fiscal }}"
                                                    data-cp="{{ $c->postal_code }}"
                                                    {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                                    {{ $c->name }}
                                                    @if($c->rfc) — {{ $c->rfc }} @endif
                                                </option>
                                                @endforeach
                                            </select>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Uso de CFDI*
                                            <select name="uso_cfdi" id="uso_cfdi" class="form-control selectpicker">
                                                <option value="G01" {{ old('uso_cfdi')=='G01' ? 'selected':'' }}>G01 — Adquisición de mercancias</option>
                                                <option value="G02" {{ old('uso_cfdi')=='G02' ? 'selected':'' }}>G02 — Devoluciones, descuentos o bonificaciones</option>
                                                <option value="G03" {{ old('uso_cfdi')=='G03' ? 'selected':'' }}>G03 — Gastos en general</option>
                                                <option value="I01" {{ old('uso_cfdi')=='I01' ? 'selected':'' }}>I01 — Construcciones</option>
                                                <option value="I02" {{ old('uso_cfdi')=='I02' ? 'selected':'' }}>I02 — Mobilario y equipo de oficina</option>
                                                <option value="I03" {{ old('uso_cfdi')=='I03' ? 'selected':'' }}>I03 — Equipo de transporte</option>
                                                <option value="I04" {{ old('uso_cfdi')=='I04' ? 'selected':'' }}>I04 — Equipo de computo y accesorios</option>
                                                <option value="I05" {{ old('uso_cfdi')=='I05' ? 'selected':'' }}>I05 — Dados, troqueles, moldes, matrices y herramental</option>
                                                <option value="I06" {{ old('uso_cfdi')=='I06' ? 'selected':'' }}>I06 — Comunicaciones telefónicas</option>
                                                <option value="I07" {{ old('uso_cfdi')=='I07' ? 'selected':'' }}>I07 — Comunicaciones satelitales</option>
                                                <option value="I08" {{ old('uso_cfdi')=='I08' ? 'selected':'' }}>I08 — Otra maquinaria y equipo</option>
                                                <option value="D01" {{ old('uso_cfdi')=='D01' ? 'selected':'' }}>D01 — Honorarios médicos, dentales y gastos hospitalarios</option>
                                                <option value="D10" {{ old('uso_cfdi')=='D10' ? 'selected':'' }}>D10 — Pagos por servicios educativos (colegiaturas)</option>
                                                <option value="S01" {{ old('uso_cfdi')=='S01' ? 'selected':'' }}>S01 — Sin efectos fiscales</option>
                                                <option value="CP01" {{ old('uso_cfdi')=='CP01' ? 'selected':'' }}>CP01 — Pagos</option>
                                            </select>
                                        </label>
                                    </div>
                                </div>
                                <div class="row mt-2" id="infoFiscalCliente" style="display:none;">
                                    <div class="col-md-12">
                                        <small class="text-muted">
                                            RFC: <span id="rfcCliente">—</span> &nbsp;|&nbsp;
                                            Régimen: <span id="regimenCliente">—</span> &nbsp;|&nbsp;
                                            C.P.: <span id="cpCliente">—</span>
                                        </small>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ── Datos de pago ───────────────────────────────────── --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <strong>Datos de pago</strong>
                            <div class="d-flex flex-column align-items-end" style="gap:3px;">
                                <button type="button" id="btnToggleRelacionado" class="btn-sustituye"
                                        onclick="toggleRelacionado()">
                                    <i class="fa fa-chain"></i>
                                    <span id="lblSustituye">Esta factura sustituye a otra</span>
                                </button>
                                <small id="avisoDemoSust" style="display:none; color:#856404; font-size:.72rem;">
                                    <i class="fa fa-lock"></i> No disponible en Pre Timbrado
                                </small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Método de Pago*
                                        <select name="metodo_pago" class="form-control selectpicker">
                                            <option value="PUE" {{ old('metodo_pago','PUE')=='PUE' ? 'selected':'' }}>PUE — Pago en una sola exhibición</option>
                                            <option value="PPD" {{ old('metodo_pago')=='PPD' ? 'selected':'' }}>PPD — Pago en parcialidades o diferido</option>
                                        </select>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label>Forma de Pago*
                                        <select name="forma_pago" class="form-control selectpicker">
                                            <option value="01" {{ old('forma_pago','01')=='01' ? 'selected':'' }}>01 — Efectivo</option>
                                            <option value="02" {{ old('forma_pago')=='02' ? 'selected':'' }}>02 — Cheque nominativo</option>
                                            <option value="03" {{ old('forma_pago')=='03' ? 'selected':'' }}>03 — Transferencia electrónica</option>
                                            <option value="04" {{ old('forma_pago')=='04' ? 'selected':'' }}>04 — Tarjeta de crédito</option>
                                            <option value="28" {{ old('forma_pago')=='28' ? 'selected':'' }}>28 — Tarjeta de débito</option>
                                            <option value="99" {{ old('forma_pago')=='99' ? 'selected':'' }}>99 — Por definir</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Factura sustituta (cancelación motivo 01) ──────────── --}}
                    <div class="card mb-3" id="bloqueRelacionado" style="display:none;">
                        <div class="card-header" style="background:#fff8e1; border-color:#f0ad4e;">
                            <strong><i class="fa fa-chain"></i> Esta factura sustituye a otra</strong>
                            <small class="text-muted ml-2">Selecciona la factura original que será cancelada con motivo 01</small>
                        </div>
                        <div class="card-body p-2">

                            {{-- Factura seleccionada --}}
                            <div id="facturaSeleccionadaWrap" style="display:none;" class="alert alert-warning py-2 px-3 mb-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <strong>Factura seleccionada:</strong>
                                    <span id="facturaSeleccionadaLabel"></span><br>
                                    <small class="text-monospace" id="uuidSeleccionadoLabel"></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ml-3" onclick="limpiarFacturaRelacionada()">
                                    <i class="fa fa-times"></i> Quitar
                                </button>
                            </div>

                            <input type="hidden" name="relacionado_uuid" id="relacionado_uuid" value="{{ old('relacionado_uuid') }}">

                            {{-- Buscador + tabla --}}
                            <div id="tablaFacturasWrap">
                                <div class="mb-2 px-1">
                                    <input type="text" id="buscarFacturaOrig" class="form-control form-control-sm"
                                           placeholder="Buscar por #, receptor o UUID..." oninput="filtrarFacturasOrig(this.value)">
                                </div>
                                @if($facturasTimbradas->isEmpty())
                                    <p class="text-muted text-center py-3">No hay facturas timbradas disponibles.</p>
                                @else
                                <div class="table-responsive" style="max-height:220px; overflow-y:auto;">
                                    <table class="table table-sm table-hover mb-0" id="tablaFacturasOrig">
                                        <thead class="thead-light" style="position:sticky;top:0;">
                                            <tr>
                                                <th>#</th>
                                                <th>Receptor</th>
                                                <th>Total</th>
                                                <th>Fecha</th>
                                                <th>UUID</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($facturasTimbradas as $ft)
                                            <tr class="fila-orig" style="cursor:pointer;"
                                                data-uuid="{{ $ft->uuid }}"
                                                data-label="#{{ $ft->id }} — {{ $ft->customer?->name ?? 'Público general' }} — ${{ number_format($ft->total,2) }}"
                                                onclick="seleccionarFacturaRelacionada(this)">
                                                <td>{{ $ft->id }}</td>
                                                <td>{{ $ft->customer?->name ?? 'Público general' }}</td>
                                                <td>${{ number_format($ft->total, 2) }}</td>
                                                <td style="white-space:nowrap;font-size:.8em;">
                                                    {{ $ft->created_at?->format('d/m/Y H:i') }}
                                                </td>
                                                <td style="font-size:.75em; color:#6c757d; max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                    {{ $ft->uuid }}
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-xs btn-warning">
                                                        <i class="fa fa-check"></i> Seleccionar
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    {{-- ── Selección de ventas ─────────────────────────────── --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>Ventas a incluir en la factura</strong>
                            <small class="text-muted ml-2">(solo ventas cobradas sin factura timbrada)</small>
                        </div>
                        <div class="card-body">
                            @if($ventas->isEmpty())
                            <p class="text-muted text-center">No hay ventas disponibles para facturar.</p>
                            @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm tabla-ventas">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width:40px;">
                                                <input type="checkbox" id="checkAll" title="Seleccionar todas">
                                            </th>
                                            <th>Folio</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Forma Pago</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ventas as $v)
                                        @php
                                            $fpMap = ['efectivo'=>'01','tarjeta_credito'=>'04','tarjeta_debito'=>'28','transferencia'=>'03'];
                                            // venta mixta (efectivo + tarjeta): no existe un FormaPago SAT que la represente,
                                            // se sugiere "99 Por definir" para forzar a elegir manualmente en vez de asumir Efectivo.
                                            $fpSugerido = $v->paymentMethod?->pay_method === 'PPD' ? '99' : ($v->type_payment === 'mixto' ? '99' : ($fpMap[$v->type_payment] ?? '01'));
                                        @endphp
                                        <tr onclick="toggleVenta(this)"
                                            data-forma-pago="{{ $fpSugerido }}"
                                            data-metodo-pago="{{ $v->paymentMethod?->pay_method ?? 'PUE' }}"
                                            class="{{ in_array($v->id, (array) old('sale_ids', $sale_id ? [$sale_id] : [])) ? 'seleccionada' : '' }}">
                                            <td onclick="event.stopPropagation()">
                                                <input type="checkbox" name="sale_ids[]"
                                                       value="{{ $v->id }}"
                                                       class="chk-venta"
                                                       data-subtotal="{{ $v->getDetailsTotales('subtotal') }}"
                                                       data-iva="{{ $v->getDetailsTotales('iva') }}"
                                                       {{ in_array($v->id, (array) old('sale_ids', $sale_id ? [$sale_id] : [])) ? 'checked' : '' }}
                                                       onchange="actualizarResumen()">
                                            </td>
                                            <td>{{ $v->folio }}</td>
                                            <td>{{ $v->getClient?->name ?? 'Público general' }}</td>
                                            <td>{{ $v->updated_at ? $v->updated_at->format('d/m/Y H:i') : $v->date }}</td>
                                            <td>
                                                {{ $v->type_payment == 'mixto' ? 'Mixto' : ucfirst($v->type_payment) }}
                                                @if($v->type_payment == 'mixto')
                                                <br><small class="text-muted">Efvo ${{ number_format($v->monto_efectivo, 2) }} / Tarj ${{ number_format($v->monto_tarjeta, 2) }}</small>
                                                @endif
                                            </td>
                                            <td>${{ number_format($v->total_sale, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- ── Resumen totales ─────────────────────────────────── --}}
                    <div id="resumen" class="p-3 mb-3" style="display:none;">
                        <div class="row text-right">
                            <div class="col-md-8 offset-md-4">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td>Subtotal:</td><td id="resSubtotal" class="font-weight-bold">$0.00</td></tr>
                                    <tr><td>IVA (16%):</td><td id="resIva">$0.00</td></tr>
                                    <tr class="border-top"><td><strong>Total:</strong></td><td id="resTotal" class="font-weight-bold text-primary">$0.00</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap mt-2" style="gap:12px;">
                        <label class="modo-timbrado-toggle" for="preTimbrado" id="lblModoTimbrado">
                            <input type="checkbox" id="preTimbrado" name="pre_timbrado" value="1"
                                   {{ old('pre_timbrado') ? 'checked' : '' }}
                                   onchange="actualizarModo(this.checked)">
                            <span class="modo-track">
                                <span class="modo-thumb"></span>
                            </span>
                            <span class="modo-label" id="textoModo">
                                <i class="fa fa-check-circle text-success"></i>
                                <span id="modoNombre">Producción</span>
                                <small id="modoDesc">factura con validez fiscal</small>
                            </span>
                        </label>
                        <div>
                            <a href="{{ route('facturas.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary" id="btnTimbrar">
                                <i class="fa fa-file-text-o"></i> Timbrar Factura
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <script>
    function toggleRelacionado() {
        var bloque  = document.getElementById('bloqueRelacionado');
        var btn     = document.getElementById('btnToggleRelacionado');
        var lbl     = document.getElementById('lblSustituye');
        var visible = bloque.style.display !== 'none';
        bloque.style.display = visible ? 'none' : '';
        btn.classList.toggle('activo', !visible);
        lbl.textContent = !visible ? 'Quitar relación' : 'Esta factura sustituye a otra';
        if (visible) limpiarFacturaRelacionada();
    }

    function seleccionarFacturaRelacionada(row) {
        var uuid  = row.dataset.uuid;
        var label = row.dataset.label;
        document.getElementById('relacionado_uuid').value = uuid;
        document.getElementById('facturaSeleccionadaLabel').textContent = label;
        document.getElementById('uuidSeleccionadoLabel').textContent = uuid;
        document.getElementById('facturaSeleccionadaWrap').style.display = '';
        document.getElementById('tablaFacturasWrap').style.display = 'none';
        document.querySelectorAll('.fila-orig').forEach(function(r) {
            r.style.background = r === row ? '#fff3cd' : '';
        });
    }

    function limpiarFacturaRelacionada() {
        document.getElementById('relacionado_uuid').value = '';
        document.getElementById('facturaSeleccionadaWrap').style.display = 'none';
        document.getElementById('tablaFacturasWrap').style.display = '';
        document.querySelectorAll('.fila-orig').forEach(function(r) { r.style.background = ''; });
    }

    function filtrarFacturasOrig(q) {
        q = q.toLowerCase();
        document.querySelectorAll('#tablaFacturasOrig .fila-orig').forEach(function(row) {
            var texto = row.textContent.toLowerCase();
            row.style.display = texto.includes(q) ? '' : 'none';
        });
    }

    function actualizarModo(esPrueba) {
        var wrap  = document.getElementById('lblModoTimbrado');
        var icono = wrap.querySelector('.modo-label i');
        document.getElementById('modoNombre').textContent = esPrueba ? 'Pre Timbrado' : 'Producción';
        document.getElementById('modoDesc').textContent   = esPrueba ? 'modo prueba — sin valor fiscal' : 'factura con validez fiscal';
        icono.className = esPrueba ? 'fa fa-flask text-warning' : 'fa fa-check-circle text-success';
        wrap.classList.toggle('es-prueba', esPrueba);

        // Bloquear sustitución en modo prueba
        var btn      = document.getElementById('btnToggleRelacionado');
        var bloque   = document.getElementById('bloqueRelacionado');
        var avisoDemo = document.getElementById('avisoDemoSust');
        if (esPrueba) {
            btn.disabled = true;
            btn.title    = 'No disponible en Pre Timbrado — los UUIDs de prueba no son válidos ante el SAT';
            btn.classList.remove('activo');
            bloque.style.display = 'none';
            document.getElementById('lblSustituye').textContent = 'Esta factura sustituye a otra';
            limpiarFacturaRelacionada();
            if (avisoDemo) avisoDemo.style.display = '';
        } else {
            btn.disabled = false;
            btn.title    = '';
            if (avisoDemo) avisoDemo.style.display = 'none';
        }
    }

    function toggleReceptor(esPublico) {
        document.getElementById('bloqueCliente').style.display = esPublico ? 'none' : '';
        if (esPublico) {
            document.getElementById('customer_id').value = '';
            document.getElementById('uso_cfdi').value = 'S01';
        }
        // refrescar selectpicker
        if (typeof $ !== 'undefined') {
            $('#customer_id, #uso_cfdi').selectpicker('refresh');
        }
    }

    function toggleVenta(row) {
        var chk = row.querySelector('.chk-venta');
        chk.checked = !chk.checked;
        row.classList.toggle('seleccionada', chk.checked);
        actualizarResumen();
    }

    function actualizarResumen() {
        var subtotal = 0, iva = 0;
        document.querySelectorAll('.chk-venta:checked').forEach(function(chk) {
            subtotal += parseFloat(chk.dataset.subtotal || 0);
            iva      += parseFloat(chk.dataset.iva || 0);
        });
        var total = subtotal + iva;
        document.getElementById('resSubtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('resIva').textContent      = '$' + iva.toFixed(2);
        document.getElementById('resTotal').textContent    = '$' + total.toFixed(2);
        document.getElementById('resumen').style.display   = subtotal > 0 ? '' : 'none';
        sugerirFormaPago();
    }

    function sugerirFormaPago() {
        var seleccionadas = document.querySelectorAll('.chk-venta:checked');
        if (seleccionadas.length !== 1) return; // solo sugerir cuando hay exactamente 1 venta
        var row = seleccionadas[0].closest('tr');
        var fp  = row.dataset.formaPago;
        var mp  = row.dataset.metodoPago;
        if (!fp) return;
        var selFp = document.querySelector('select[name="forma_pago"]');
        var selMp = document.querySelector('select[name="metodo_pago"]');
        if (selFp) { selFp.value = fp; $(selFp).selectpicker('val', fp); }
        if (selMp) { selMp.value = mp; $(selMp).selectpicker('val', mp); }
    }

    // Seleccionar todas
    document.getElementById('checkAll')?.addEventListener('change', function() {
        document.querySelectorAll('.chk-venta').forEach(function(chk) {
            chk.checked = this.checked;
            chk.closest('tr').classList.toggle('seleccionada', this.checked);
        }.bind(this));
        actualizarResumen();
    });

    // Mostrar info fiscal del cliente al seleccionarlo
    document.getElementById('customer_id')?.addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('rfcCliente').textContent     = opt.dataset.rfc     || '—';
            document.getElementById('regimenCliente').textContent = opt.dataset.regimen || '—';
            document.getElementById('cpCliente').textContent      = opt.dataset.cp      || '—';
            document.getElementById('infoFiscalCliente').style.display = '';
        } else {
            document.getElementById('infoFiscalCliente').style.display = 'none';
        }
    });

    // Inicializar resumen con ventas ya marcadas (old input)
    actualizarResumen();
    // Inicializar estado del receptor
    toggleReceptor(document.getElementById('publiCoGeneral').checked);
    // Inicializar bloque relacionado si hay old input
    (function(){
        var uuid = document.getElementById('relacionado_uuid').value;
        if (!uuid) return;
        document.getElementById('bloqueRelacionado').style.display = '';
        document.getElementById('btnToggleRelacionado').classList.add('activo');
        document.getElementById('lblSustituye').textContent = 'Quitar relación';
        // Buscar la fila correspondiente y simular selección
        var fila = document.querySelector('.fila-orig[data-uuid="' + uuid + '"]');
        if (fila) seleccionarFacturaRelacionada(fila);
    })();
    // Inicializar modo timbrado
    actualizarModo(document.getElementById('preTimbrado').checked);
    </script>
</body>
</html>
