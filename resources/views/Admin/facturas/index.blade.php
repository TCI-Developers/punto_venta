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

                {{-- Filtros --}}
                <div class="filtros-facturas mb-4">
                    <div class="filtros-row">
                        <div class="filtro-select-wrap">
                            <i class="fa fa-filter filtro-icon"></i>
                            <select id="filtroStatus" class="filtro-select" onchange="aplicarFiltros()">
                                <option value="">Todos los estados</option>
                                <option value="0">Pendiente</option>
                                <option value="1">Timbrada</option>
                                <option value="2">Cancelada</option>
                                <option value="3">Error</option>
                            </select>
                        </div>
                        <div class="filtro-tabs" role="group">
                            <button type="button" id="btnTodos" class="ftab ftab-active" onclick="setFiltroDemo('')">
                                <i class="fa fa-list"></i> Todas
                            </button>
                            <button type="button" id="btnProduccion" class="ftab ftab-prod" onclick="setFiltroDemo('0')">
                                <i class="fa fa-check-circle"></i> Producción
                            </button>
                            <button type="button" id="btnPrueba" class="ftab ftab-demo" onclick="setFiltroDemo('1')">
                                <i class="fa fa-flask"></i> Pre Timbrado
                            </button>
                        </div>
                    </div>
                </div>
                <style>
                .filtros-facturas { background: #f8f9fa; border-radius: 10px; padding: 14px 18px; border: 1px solid #e3e6ea; }
                .filtros-row { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
                .filtro-select-wrap { position: relative; display: flex; align-items: center; }
                .filtro-icon { position: absolute; left: 10px; color: #6c757d; font-size: 0.85rem; pointer-events: none; }
                .filtro-select { appearance: none; padding: 7px 32px 7px 30px; border: 1.5px solid #ced4da; border-radius: 8px; font-size: 0.9rem; background: #fff; color: #495057; cursor: pointer; min-width: 190px; transition: border-color .2s; }
                .filtro-select:focus { outline: none; border-color: #80bdff; box-shadow: 0 0 0 2px rgba(0,123,255,.15); }
                .filtro-tabs { display: flex; gap: 6px; background: #e9ecef; border-radius: 10px; padding: 4px; }
                .ftab { border: none; background: transparent; padding: 6px 14px; border-radius: 7px; font-size: 0.85rem; font-weight: 500; cursor: pointer; color: #6c757d; transition: all .2s; white-space: nowrap; }
                .ftab:hover { background: rgba(255,255,255,.7); color: #343a40; }
                .ftab-active { background: #fff !important; color: #0056b3 !important; box-shadow: 0 1px 4px rgba(0,0,0,.12); }
                .ftab-prod.ftab-sel { background: #fff !important; color: #155724 !important; box-shadow: 0 1px 4px rgba(0,0,0,.12); }
                .ftab-demo.ftab-sel { background: #fff !important; color: #856404 !important; box-shadow: 0 1px 4px rgba(0,0,0,.12); }
                </style>

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
                            <tr data-status="{{ $f->status }}" data-demo="{{ $f->is_demo ? '1' : '0' }}">
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
                                    @if($f->is_demo)
                                    <span class="badge badge-warning">Prueba</span>
                                    @endif
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
                                    <a href="{{ route('facturas.cancelForm', $f->id) }}"
                                       class="btn btn-xs btn-danger"
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
    var filtroDemo = '';

    function setFiltroDemo(val) {
        filtroDemo = val;
        document.getElementById('btnTodos').className      = val === ''  ? 'ftab ftab-active' : 'ftab';
        document.getElementById('btnProduccion').className = val === '0' ? 'ftab ftab-prod ftab-sel' : 'ftab ftab-prod';
        document.getElementById('btnPrueba').className     = val === '1' ? 'ftab ftab-demo ftab-sel' : 'ftab ftab-demo';
        aplicarFiltros();
    }

    function aplicarFiltros() {
        var status = document.getElementById('filtroStatus').value;
        document.querySelectorAll('#tablaFacturas tbody tr[data-status]').forEach(function(row) {
            var matchStatus = status === '' || row.dataset.status === status;
            var matchDemo   = filtroDemo === '' || row.dataset.demo === filtroDemo;
            row.style.display = (matchStatus && matchDemo) ? '' : 'none';
        });
    }
    </script>
</body>
</html>
