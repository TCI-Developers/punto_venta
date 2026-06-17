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
                        <div class="card-header bg-light"><strong>Datos de pago</strong></div>
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
                                        <tr onclick="toggleVenta(this)" class="{{ in_array($v->id, (array) old('sale_ids', $sale_id ? [$sale_id] : [])) ? 'seleccionada' : '' }}">
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
                                            <td>{{ ucfirst($v->type_payment) }}</td>
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

                    <div class="text-right">
                        <a href="{{ route('facturas.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnTimbrar">
                            <i class="fa fa-file-invoice"></i> Timbrar Factura
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <script>
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
    </script>
</body>
</html>
