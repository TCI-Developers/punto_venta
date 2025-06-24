<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
        <div class="card card-primary" style="height:90vh;">
            <div class="card-header">
                <h2 class="text-center">
                    <a href="{{route('devoluciones.index')}}" class="btn btn-success btn-sm float-left"><i class="fa fa-arrow-left"></i></a>
                    Listado de ventas
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="table">
                        <thead>
                            <tr class="text-center table-info">
                                <th colspan="5">VENTAS</th>
                            </tr>
                            <tr class="text-center">
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total Venta</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $item)
                                @if(!is_object($item->hasDevolution))
                                <tr class="text-center">
                                    <td>{{$item->folio}}</td>
                                    <td>{{$item->customer->name}}</td>
                                    <td>{{date('d-m-Y', strtotime($item->date))}}</td>
                                    <td>$ {{number_format($item->total_sale)}}</td>
                                    <td><a href="{{route('devoluciones.createSaleToDevolucion', $item->id)}}" class="btn btn-warning"><i class="fa fa-undo"></i></a></td>
                                </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="5" class="table-warnign">Sin registros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</main>   
</body>
</html>