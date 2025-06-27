<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <script src="{{asset('js/branchs/show.js')}}"></script>
</head>
<body>
    @include('components.use.nav-slider')
    @include('components.use.notification_success_error')

    <main class="content" style="height:100vh;">

        <div class="card card-primary">
            <div class="form-group card-header with-border text-center">
                <h2>Sucursal</h2>
            </div>
            <div class="col-12 mt-2">
                <a href="{{route('branchs.index')}}" class="btn btn-success"><i class="fa fa-arrow-left"></i></a>
                @isset($branch)
                {{--<button type="button" class="btn btn-primary float-right col-2" onclick="importAll({{$branch->id}}, 'productos')"><i class="fa fa-download"></i> Importar productos</button>
                <button type="button" class="btn btn-warning float-right col-2 mr-2" onclick="importAll({{$branch->id}}, 'choferes')"><i class="fa fa-download"></i> Importar choferes</button>
                <button type="button" class="btn btn-secondary float-right col-2 mr-2" onclick="importAll({{$branch->id}}, 'metodos_de_pago')"><i class="fa fa-download"></i> Importar metodos de pago</button>
                <button type="button" class="btn btn-info float-right col-2 mr-2" onclick="importAll({{$branch->id}}, 'unidades_Sat')"><i class="fa fa-download"></i> Importar unidades Sat</button>
                <button type="button" class="btn btn-light float-right col-2 mr-2" onclick="importAll({{$branch->id}}, 'proveedores')"><i class="fa fa-download"></i> Importar Proveedores</button>--}}
                @endisset
            </div>
            <div class="card-body" style="height:70vh;">
                <form action="{{route('branchs.store', isset($branch) ? $branch->id:'')}}" method="post">
                    @csrf
                    <input type="hidden" name="status" value="{{$status}}">
                    <div class="row">
                        <label class="col-lg-12">Nombre Sucursal*
                            <input type="text" name="name" class="form-control inputs" value="{{$branch->name ?? ''}}" {{$status ? 'readonly' : ''}} required>
                        </label>
                        <label class="col-lg-12">Razón Social
                            <input type="text" name="razon_social" class="form-control inputs" value="{{$branch->razon_social ?? ''}}" {{$status ? 'readonly' : ''}}>
                        </label>
                        <label class="col-lg-12">Dirección
                            <input type="text" name="address" class="form-control inputs" value="{{$branch->address ?? ''}}" {{$status ? 'readonly' : ''}}>
                        </label>
                        <label class="col-lg-4">RFC
                            <input type="text" name="rfc" class="form-control inputs" value="{{$branch->rfc ?? ''}}" {{$status ? 'readonly' : ''}}>
                        </label>
                        <label class="col-lg-4">Teléfono
                            <input type="phone" name="phone" class="form-control inputs" value="{{$branch->phone ?? ''}}" {{$status ? 'readonly' : ''}}>
                        </label>
                        <label class="col-lg-4">Usuarios
                            <select name="user_id[]" id="user_id" class="form-control" multiple {{$status ? 'disabled' : ''}}>
                                @foreach($users as $item)
                                    @php
                                        $selected = '';
                                        if (isset($users_exist_in_branch) && $users_exist_in_branch !== 'false') {
                                            foreach ($users_exist_in_branch as $user_exist) {
                                                if ($item->id == $user_exist->user_id) {
                                                    $selected = 'selected';
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <option value="{{$item->id}}" {{$selected}}>{{$item->name}}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-primary {{$status ? 'd-none':''}}" id="btnSubmit"><i class="fa fa-save"></i> {{$status ? 'Actualizar' : 'Guardar'}}</button>
                            @if(isset($branch))
                                @if(auth()->user()->hasPermissionThroughModule('sucursales','punto_venta','update'))
                                <button type="button" class="btn btn-success" id="btnEdit" onClick="edit()"><i class="fa fa-pencil"></i> Habilitar edición</button>
                                @endif
                            <button type="button" class="btn btn-light d-none" id="btnCancel" onClick="cancel()"><i class="fa fa-times"></i> Cancelar</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </main>
</body>
</html>
