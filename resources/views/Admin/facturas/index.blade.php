<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')

        <div class="card card-primary">
            <div class="form-group card-header with-border text-center">
                <h2>
                    <a href="{{ route('sale.index') }}" class="btn btn-success float-left btn-sm"
                       data-toggle="tooltip" title="Regresar"><i class="fa fa-arrow-left"></i></a>
                    Facturas
                    @if(auth()->user()->hasPermissionThroughModule('ventas','punto_venta','create'))
                    <a href="{{ route('facturas.create') }}" class="btn btn-primary float-right btn-sm">
                        <i class="fa fa-plus"></i> Nueva Factura
                    </a>
                    @endif
                </h2>
            </div>
            <div class="card-body">

                {{-- Filtro por status --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="filtroStatus" class="form-control" onchange="filtrarStatus(this.value)">
                            <option value="">Todos los estados</option>
                            <option value="0">Pendiente</option>
                            <option value="1">Timbrada</option>
                            <option value="2">Cancelada</option>
                            <option value="3">Error</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm" id="tablaFacturas">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Folio SAT</th>
                                <th>Ventas</th>
                                <th>Receptor</th>
                                <th>Subtotal</th>
                                <th>IVA</th>
                                <th>Total</th>
                                <th>F.Pago</th>
                                <th>M.Pago</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($facturas as $f)
                            <tr data-status="{{ $f->status }}">
                                <td>{{ $f->id }}</td>
                                <td style="font-size:0.8em;">
                                    {{ $f->folio_fiscal ? substr($f->folio_fiscal, 0, 8).'...' : '—' }}
                                </td>
                                <td>{{ $f->sales->count() }}</td>
                                <td>
                                    @if($f->customer_id)
                                        {{ $f->customer?->name ?? '—' }}
                                    @else
                                        <span class="text-muted">Público general</span>
                                    @endif
                                </td>
                                <td>${{ number_format($f->subtotal, 2) }}</td>
                                <td>${{ number_format($f->iva, 2) }}</td>
                                <td><strong>${{ number_format($f->total, 2) }}</strong></td>
                                <td>{{ $f->forma_pago }}</td>
                                <td>{{ $f->metodo_pago }}</td>
                                <td>
                                    <span class="badge badge-{{ $f->getStatusBadge() }}">
                                        {{ $f->getStatusLabel() }}
                                    </span>
                                </td>
                                <td style="white-space:nowrap;font-size:0.85em;">
                                    {{ $f->created_at ? $f->created_at->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="{{ route('facturas.show', $f->id) }}"
                                       class="btn btn-xs btn-info" title="Ver detalle">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if($f->status == 1 && auth()->user()->hasPermissionThroughModule('ventas','punto_venta','destroy'))
                                    <a href="{{ route('facturas.cancel', $f->id) }}"
                                       class="btn btn-xs btn-danger"
                                       onclick="return confirm('¿Cancelar esta factura? Esta acción no se puede deshacer.')"
                                       title="Cancelar factura">
                                        <i class="fa fa-ban"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">No hay facturas registradas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
    function filtrarStatus(val) {
        document.querySelectorAll('#tablaFacturas tbody tr[data-status]').forEach(function(row) {
            row.style.display = (val === '' || row.dataset.status === val) ? '' : 'none';
        });
    }
    </script>
</body>
</html>
