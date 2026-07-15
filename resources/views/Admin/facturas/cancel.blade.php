<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Factura #{{ $factura->id }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')

        <div class="card card-danger">
            <div class="form-group card-header with-border text-center">
                <h2>
                    <a href="{{ route('facturas.show', $factura->id) }}" class="btn btn-success float-left btn-sm"
                       data-toggle="tooltip" title="Regresar"><i class="fa fa-arrow-left"></i></a>
                    Cancelar Factura #{{ $factura->id }}
                </h2>
            </div>
            <div class="card-body">

                {{-- Datos de la factura --}}
                <div class="alert alert-warning">
                    <strong>UUID:</strong> {{ $factura->uuid ?? '—' }}<br>
                    <strong>Receptor:</strong>
                    {{ $factura->customer?->razon_social ?? $factura->customer?->name ?? 'PUBLICO EN GENERAL' }}<br>
                    <strong>Total:</strong> ${{ number_format($factura->total, 2) }}
                </div>

                <form action="{{ route('facturas.cancel', $factura->id) }}" method="POST" id="formCancelacion">
                    @csrf
                    @method('POST')

                    <div class="form-group">
                        <label><strong>Motivo de cancelación* </strong>
                            <select name="motivo" id="motivo" class="form-control" onchange="toggleFolioSust(this.value)" required>
                                <option value="">— Selecciona el motivo —</option>
                                <option value="01">01 — Comprobante emitido con errores con relación (se sustituye por otra factura)</option>
                                <option value="02">02 — Comprobante emitido con errores sin relación</option>
                                <option value="03">03 — No se llevó a cabo la operación</option>
                                <option value="04">04 — Operación nominativa relacionada en factura global</option>
                            </select>
                        </label>
                        @error('motivo') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Solo visible si motivo = 01 --}}
                    <div id="bloquesFolioSust" style="display:none;">
                        <input type="hidden" name="foliosust" id="foliosust" value="">

                        {{-- Factura seleccionada --}}
                        <div id="sustSeleccionadaWrap" style="display:none;" class="alert alert-success py-2 px-3 mb-2 d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fa fa-check-circle"></i>
                                <strong>Factura sustituta:</strong>
                                <span id="sustSeleccionadaLabel"></span><br>
                                <small class="text-monospace" id="sustUuidLabel" style="font-size:.78em;"></small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ml-3" onclick="limpiarSustituta()">
                                <i class="fa fa-times"></i> Quitar
                            </button>
                        </div>

                        @error('foliosust') <div class="alert alert-danger py-1 px-3">{{ $message }}</div> @enderror

                        <div id="tablaSustWrap">
                            @if($sustitutasVinculadas->isNotEmpty())
                            <p class="mb-1"><small class="text-success font-weight-bold"><i class="fa fa-link"></i> Ya vinculadas a esta factura</small></p>
                            <div class="table-responsive mb-2" style="max-height:160px; overflow-y:auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light" style="position:sticky;top:0;">
                                        <tr><th>#</th><th>Receptor</th><th>Total</th><th>Fecha</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sustitutasVinculadas as $sv)
                                        <tr class="fila-sust" style="cursor:pointer;"
                                            data-uuid="{{ $sv->uuid }}"
                                            data-label="#{{ $sv->id }} — {{ $sv->customer?->name ?? 'Público general' }} — ${{ number_format($sv->total,2) }}"
                                            onclick="seleccionarSustituta(this)">
                                            <td>{{ $sv->id }}</td>
                                            <td>{{ $sv->customer?->name ?? 'Público general' }}</td>
                                            <td>${{ number_format($sv->total, 2) }}</td>
                                            <td style="font-size:.8em;white-space:nowrap;">{{ $sv->created_at?->format('d/m/Y H:i') }}</td>
                                            <td><button type="button" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Seleccionar</button></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            @if($sustitutasOtras->isNotEmpty())
                            <p class="mb-1"><small class="text-muted font-weight-bold">Otras facturas timbradas</small></p>
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm" placeholder="Buscar por #, receptor o UUID..."
                                       oninput="filtrarSustituta(this.value)">
                            </div>
                            <div class="table-responsive" style="max-height:180px; overflow-y:auto;">
                                <table class="table table-sm table-hover mb-0" id="tablaOtrasSust">
                                    <thead class="thead-light" style="position:sticky;top:0;">
                                        <tr><th>#</th><th>Receptor</th><th>Total</th><th>Fecha</th><th>UUID</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sustitutasOtras as $so)
                                        <tr class="fila-sust fila-otras" style="cursor:pointer;"
                                            data-uuid="{{ $so->uuid }}"
                                            data-label="#{{ $so->id }} — {{ $so->customer?->name ?? 'Público general' }} — ${{ number_format($so->total,2) }}"
                                            onclick="seleccionarSustituta(this)">
                                            <td>{{ $so->id }}</td>
                                            <td>{{ $so->customer?->name ?? 'Público general' }}</td>
                                            <td>${{ number_format($so->total, 2) }}</td>
                                            <td style="font-size:.8em;white-space:nowrap;">{{ $so->created_at?->format('d/m/Y H:i') }}</td>
                                            <td style="font-size:.75em;color:#6c757d;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $so->uuid }}</td>
                                            <td><button type="button" class="btn btn-xs btn-warning"><i class="fa fa-check"></i> Seleccionar</button></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            @if($sustitutasVinculadas->isEmpty() && $sustitutasOtras->isEmpty())
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i>
                                No hay facturas timbradas disponibles. Primero crea la factura sustituta con
                                <a href="{{ route('facturas.create') }}">Nueva Factura</a>
                                activando <em>"Esta factura sustituye a otra"</em>.
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="alert alert-info" id="infoMotivo" style="display:none;"></div>

                    <div class="mt-3">
                        <a href="{{ route('facturas.show', $factura->id) }}" class="btn btn-secondary mr-2">
                            <i class="fa fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger" id="btnConfirmar" disabled
                                onclick="return confirm('¿Confirmas la cancelación de esta factura? Esta acción se enviará al SAT.')">
                            <i class="fa fa-ban"></i> Confirmar cancelación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
    var motivos = {
        '01': '<strong>Paso 1 completado:</strong> ya timbré la factura sustituta con el enlace a esta. ' +
              'Ingresa abajo el UUID de esa nueva factura. ' +
              '<br><span class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' +
              'Si aún no has creado la factura sustituta, cancela este proceso, ve a ' +
              '<em>Nueva Factura</em>, activa <em>"Esta factura sustituye a otra"</em> e ingresa el UUID de esta factura, ' +
              'tímbrala, y luego regresa aquí con su UUID.</span>',
        '02': 'Se emitió con errores pero <strong>no se emitirá una factura sustituta</strong>. Ejemplo: RFC del cliente incorrecto y el cliente ya no necesita la factura.',
        '03': '<strong>La venta/operación no se realizó</strong>. El cliente devolvió el producto o se canceló la operación antes de entregarse.',
        '04': 'La operación ya está incluida en una <strong>factura global (de ticket)</strong> y esta factura individual es redundante.',
    };

    function toggleFolioSust(val) {
        document.getElementById('bloquesFolioSust').style.display = (val === '01') ? '' : 'none';
        if (val !== '01') limpiarSustituta();
        var sinSust = val !== '01' || document.getElementById('foliosust').value !== '';
        document.getElementById('btnConfirmar').disabled = (val === '') || (val === '01' && !document.getElementById('foliosust').value);
        var info = document.getElementById('infoMotivo');
        if (val && motivos[val]) {
            info.innerHTML = motivos[val];
            info.style.display = '';
        } else {
            info.style.display = 'none';
        }
    }

    function seleccionarSustituta(row) {
        var uuid  = row.dataset.uuid;
        var label = row.dataset.label;
        document.getElementById('foliosust').value = uuid;
        document.getElementById('sustSeleccionadaLabel').textContent = label;
        document.getElementById('sustUuidLabel').textContent = uuid;
        document.getElementById('sustSeleccionadaWrap').style.display = '';
        document.getElementById('tablaSustWrap').style.display = 'none';
        document.getElementById('btnConfirmar').disabled = false;
    }

    function limpiarSustituta() {
        document.getElementById('foliosust').value = '';
        document.getElementById('sustSeleccionadaWrap').style.display = 'none';
        document.getElementById('tablaSustWrap').style.display = '';
        document.getElementById('btnConfirmar').disabled = true;
    }

    function filtrarSustituta(q) {
        q = q.toLowerCase();
        document.querySelectorAll('#tablaOtrasSust .fila-otras').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }
    </script>
</body>
</html>
