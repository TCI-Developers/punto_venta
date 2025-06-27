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
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Proveedores {{$status == 0 ? 'Inhabilitados':''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <div class="form-group">
                @if(auth()->user()->hasPermissionThroughModule('proveedores','punto_venta', 'create'))
                <a href="{{route('proveedor.create')}}" class="btn btn-success"><i class="fa fa-plus"></i> &nbsp; Nuevo</a>
                <a href="{{route('proveedor.index', $status == 1 ? 0:1)}}" class="btn {{$status == 1 ? 'btn-light':'btn-primary'}} float-right"><i class="fa fa-archive"></i> &nbsp; {{$status == 1 ? 'Inhabilitados':'Habilitados'}}</a>
                @endif
            </div>

            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>RFC</th>
                        <th>Telefono</th>
                        <th>Saldo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $item)
                    <tr>
                        <td class="text-center">{{$item->code_proveedor}}</td>
                        <td class="">{{$item->name}}</td>
                        <td class="text-center">{{$item->rfc}}</td>
                        <td class="text-center">{{$item->phone}}</td>
                        <td class="text-center">$ {{number_format($item->saldo, 2)}}</td>
                        <td class="text-center">
                            @if(auth()->user()->hasPermissionThroughModule('proveedores','punto_venta', 'update') || auth()->user()->hasPermissionThroughModule('proveedores','punto_venta', 'show'))
                            <a href="{{route('proveedor.show', $item->id)}}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                            @endif

                            @if(auth()->user()->hasPermissionThroughModule('proveedores','punto_venta', 'destroy'))
                                @if($status == 1)
                                <a href="{{route('proveedor.enable', [$item->id, $status])}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                @else
                                <a href="{{route('proveedor.enable', [$item->id, $status])}}" class="btn btn-primary btn-sm"><i class="fa fa-refresh"></i></a>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="table-warning text-center">Sin turnos</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    </main>   
</body>
</html>
