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

        <div class="card card-danger" style="max-width:640px; margin:0 auto;">
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
                    <div class="form-group" id="bloquesFolioSust" style="display:none;">
                        <label><strong>UUID de la factura sustituta*</strong>
                            <input type="text" name="foliosust" id="foliosust" class="form-control"
                                   placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
                                   maxlength="36">
                            <small class="text-muted">UUID de la nueva factura que reemplaza a esta (formato SAT).</small>
                        </label>
                        @error('foliosust') <span class="text-danger">{{ $message }}</span> @enderror
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
        '01': 'Se emitió con errores y <strong>se va a emitir una factura sustituta</strong>. Necesitas proporcionar el UUID de la nueva factura. El SAT vincula ambas.',
        '02': 'Se emitió con errores pero <strong>no se emitirá una factura sustituta</strong>. Ejemplo: RFC del cliente incorrecto y el cliente ya no necesita la factura.',
        '03': '<strong>La venta/operación no se realizó</strong>. El cliente devolvió el producto o se canceló la operación antes de entregarse.',
        '04': 'La operación ya está incluida en una <strong>factura global (de ticket)</strong> y esta factura individual es redundante.',
    };

    function toggleFolioSust(val) {
        document.getElementById('bloquesFolioSust').style.display = (val === '01') ? '' : 'none';
        document.getElementById('btnConfirmar').disabled = (val === '');
        var info = document.getElementById('infoMotivo');
        if (val && motivos[val]) {
            info.innerHTML = motivos[val];
            info.style.display = '';
        } else {
            info.style.display = 'none';
        }
    }
    </script>
</body>
</html>
