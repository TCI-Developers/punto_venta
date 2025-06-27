<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')

    <style>
        .uppercase{
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
<div class="card card-primary">
    <div class="form-group card-header with-border text-center">
        <h2>Empresa</h2>
    </div>
    <div class="card-body">
        <form action="{{route('admin.empresaUpdate')}}" method="post">
            @csrf
            <div class="container">
                <label for="razon_social" class="col-12">RAZÓN SOCIAL*
                    <input type="text" class="form-control uppercase" name="razon_social" id="razon_social" value="{{$empresa->razon_social ?? ''}}">
                </label>
                <label for="name" class="col-12">NOMBRE*
                    <input type="text" class="form-control uppercase" name="name" id="name" value="{{$empresa->name ?? ''}}" required>
                </label>
                <label for="rfc" class="col-12">RFC*
                    <input type="text" class="form-control" name="rfc" id="rfc" value="{{$empresa->rfc ?? ''}}" required>
                </label>
                <label for="address" class="col-12">DIRECCIÓN*
                    <input type="text" class="form-control" name="address" id="address" value="{{$empresa->address ?? ''}}" required>
                </label>
                <label for="branch_id" class="col-12">Sucursal*
                    <select class="form-control" name="branch_id" id="branch_id" >
                        <option value=""></option>
                        @foreach($branchs ?? [] as $item)
                            <option value="{{$item->id}}" {{$empresa->branch_id == $item->id ? 'selected':''}}>{{$item->name}}</option>
                        @endforeach
                    </select>
                </label>

                @if(Auth::User()->hasRole('root') || auth()->user()->hasPermissionThroughModule('empresa','punto_venta','auth'))
                <div class="form-group text-right mt-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Guardar</button>
                </div>
                @endif
            </div>
        </form>
    </div>
    
    @if(Auth::User()->hasRole(['root']) || auth()->user()->hasPermissionThroughModule('empresa','punto_venta','auth'))
    <div class="card-body">
        <hr>
            <h3 class="text-center text-bold">Importación</h3>
            @include('Admin.root.importacion_DBExt_DBLocal')
    </div>
    @endif
</div>
   </main>   
</body>
</html>