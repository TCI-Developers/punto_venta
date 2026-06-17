<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $factura->id }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <style>
        .concepto-table th { background: #f1f1f1; }
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
                    Factura #{{ $factura->id }}
                    <span class="badge badge-{{ $factura->getStatusBadge() }} ml-2">
                        {{ $factura->getStatusLabel() }}
                    </span>
                    @if($factura->status == 1 && auth()->user()->hasPermissionThroughModule('ventas','punto_venta','destroy'))
                    <a href="{{ route('facturas.cancel', $factura->id) }}"
                       class="btn btn-danger float-right btn-sm"
                       onclick="return confirm('¿Cancelar esta factura? Esta acción no se puede deshacer.')">
                        <i class="fa fa-ban"></i> Cancelar
                    </a>
                    @endif
                </h2>
            </div>
            <div class="card-body">

                <div class="row">
                    {{-- ── Datos del CFDI ──────────────────────────── --}}
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-light"><strong>Datos del CFDI</strong></div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width:130px;">UUID:</td>
                                        <td style="word-break:break-all;">
                                            {{ $factura->uuid ?? '—' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Folio Fiscal:</td>
                                        <td>{{ $factura->folio_fiscal ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Serie / Folio:</td>
                                        <td>{{ ($factura->serie ?? '') . ($factura->folio ? ' / '.$factura->folio : '—') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tipo:</td>
                                        <td>{{ $factura->tipo_comprobante }} — Ingreso</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Método Pago:</td>
                                        <td>{{ $factura->metodo_pago }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Forma Pago:</td>
                                        <td>{{ $factura->forma_pago }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Uso CFDI:</td>
                                        <td>{{ $factura->uso_cfdi }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Moneda:</td>
                                        <td>{{ $factura->moneda }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Emitida por:</td>
                                        <td>{{ $factura->user?->name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Fecha:</td>
                                        <td>{{ $factura->created_at ? $factura->created_at->format('d/m/Y H:i') : '—' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ── Receptor ──────────────────────────────────── --}}
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-light"><strong>Receptor</strong></div>
                            <div class="card-body p-2">
                                @if($factura->customer_id)
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width:130px;">Nombre:</td>
                                        <td>{{ $factura->customer?->razon_social ?? $factura->customer?->name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">RFC:</td>
                                        <td>{{ $factura->customer?->rfc ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Régimen:</td>
                                        <td>{{ $factura->customer?->regimen_fiscal ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">C.P.:</td>
                                        <td>{{ $factura->customer?->postal_code ?? '—' }}</td>
                                    </tr>
                                </table>
                                @else
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width:130px;">Nombre:</td>
                                        <td>PUBLICO EN GENERAL</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">RFC:</td>
                                        <td>XAXX010101000</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Régimen:</td>
                                        <td>616 — Sin obligaciones fiscales</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">C.P.:</td>
                                        <td>99999</td>
                                    </tr>
                                </table>
                                @endif
                            </div>
                        </div>

                        {{-- Totales --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light"><strong>Totales</strong></div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-borderless mb-0 text-right">
                                    <tr>
                                        <td class="text-muted">Subtotal:</td>
                                        <td>${{ number_format($factura->subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Descuento:</td>
                                        <td>${{ number_format($factura->descuento, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">IVA:</td>
                                        <td>${{ number_format($factura->iva, 2) }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td><strong>Total:</strong></td>
                                        <td><strong class="text-primary">${{ number_format($factura->total, 2) }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Ventas incluidas ─────────────────────────── --}}
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong>Ventas incluidas ({{ $factura->sales->count() }})</strong>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Folio</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Forma Pago</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($factura->sales as $s)
                                    <tr>
                                        <td>{{ $s->folio }}</td>
                                        <td>{{ $s->getClient?->name ?? '—' }}</td>
                                        <td>{{ $s->updated_at ? $s->updated_at->format('d/m/Y H:i') : $s->date }}</td>
                                        <td>{{ ucfirst($s->type_payment) }}</td>
                                        <td>${{ number_format($s->total_sale, 2) }}</td>
                                        <td>
                                            <a href="{{ route('sale.show', $s->id) }}"
                                               class="btn btn-xs btn-outline-secondary" target="_blank">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted">Sin ventas asociadas.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ── Error / mensaje del servicio ──────────────── --}}
                @if($factura->error_message)
                <div class="alert alert-danger">
                    <strong>Error del servicio de facturación:</strong><br>
                    {{ $factura->error_message }}
                </div>
                @endif

                {{-- ── Acciones XML / PDF ───────────────────────── --}}
                @if($factura->status == 1)
                <div class="text-right mt-2">
                    @if($factura->pdf_url)
                    <a href="{{ $factura->pdf_url }}" target="_blank" class="btn btn-danger mr-2">
                        <i class="fa fa-file-pdf"></i> Descargar PDF
                    </a>
                    @endif
                    @if($factura->xml)
                    <button class="btn btn-secondary" onclick="descargarXML()">
                        <i class="fa fa-file-code"></i> Descargar XML
                    </button>
                    @endif
                </div>
                @endif

                {{-- ── Reintento si hubo error ──────────────────── --}}
                @if($factura->status == 3)
                <div class="text-right mt-2">
                    <a href="{{ route('facturas.create') }}" class="btn btn-primary">
                        <i class="fa fa-redo"></i> Crear nueva factura
                    </a>
                </div>
                @endif

            </div>
        </div>
    </main>

    @if($factura->xml)
    <script>
    function descargarXML() {
        var xml   = @json($factura->xml);
        var blob  = new Blob([xml], { type: 'application/xml' });
        var url   = URL.createObjectURL(blob);
        var a     = document.createElement('a');
        a.href    = url;
        a.download = 'factura-{{ $factura->uuid ?? $factura->id }}.xml';
        a.click();
        URL.revokeObjectURL(url);
    }
    </script>
    @endif
</body>
</html>
