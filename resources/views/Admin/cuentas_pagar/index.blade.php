
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas por pagar</title>
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
            <h2>Cuentas por pagar</h2>
        </div>

        <div class="card-body table-responsive">
            <div class="row form-group float-right">
                <a href="{{route('cxp.index', $status == 1 ? 0:1)}}" class="btn {{$status == 1 ? 'btn-light':'btn-primary'}}">
                    <i class="fa fa-archive"></i> &nbsp; {{$status == 1 ? 'Inhabilitados':'Habilitados'}}</a>
            </div>

            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Folio Compra</th>
                        <th>Fecha Vencimiento</th>
                        <th>Total Compra</th>
                        <th>Total Pagado</th>
                        <th>Status</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentas as $item)
                    <tr>
                        <td class="text-center">{{$item->getCompra->folio}}</td>
                        <td class="text-center">{{date('d-m-Y', strtotime($item->fecha_vencimiento))}}</td>
                        <td class="text-right">$ {{number_format($item->total, 2)}}</td>
                        <td class="text-right">$ {{number_format($item->getDetails->sum('importe'), 2)}}</td>
                        <td class="text-center">
                            @if($item->status != 0)
                            <span class="badge {{$item->status == 1 ? 'badge-warning':'badge-success'}}">{{$item->status == 1 ? 'Pendiente':'Pagada'}}</span>
                            @else
                            <span class="badge badge-danger">Eliminada</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{route('cxp.show', $item->id)}}" class="btn btn-warning btn-sm"><i class="fa fa-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="table-warning text-center">Sin cuentas</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
  </div>
 </main>   
</body>
</html>