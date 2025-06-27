<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="{{asset('js/users/index.js')}}"></script>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
        <div class="card {{$status == 0 ? 'card-secondary':'card-primary'}}">
            <div class="form-group card-header with-border text-center">
                <h2>Usuarios {{$status == 0 ? 'Inhabilidatos':''}}</h2>
            </div>

            <div class="col-12">
                @if(auth()->user()->hasPermissionThroughModule('usuarios','punto_venta','create') || auth()->user()->hasPermissionThroughModule('usuarios','punto_venta','destroy'))
                <div class="card-header">
                    @if(auth()->user()->hasPermissionThroughModule('usuarios','punto_venta','create'))
                    <a class="btn btn-primary" style="cursor:pointer;" onclick="showModal()"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Nuevo usuario"><i class="fa fa-plus"></i></a>
                    @endif
                    
                    @if(auth()->user()->hasPermissionThroughModule('usuarios','punto_venta','destroy'))
                    <a href="{{route('users.index', $status == 0 ? 1:0)}}" class="btn {{$status == 0 ? 'btn-success':'btn-secondary'}} float-right" data-bs-toggle="tooltip" data-bs-placement="top" 
                    title="Usuarios {{$status == 0 ? 'Habilitados':'Inhabilitados'}}"><i class="fa fa-folder"></i></a>
                    @endif
                </div>
                @endif
            </div>

            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered datatable">
                    <thead>
                        <tr class="text-center">
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Telefono</th>
                            <th>Roles</th>
                            <th>Turno</th>
                            <th>Sucursal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $item)
                        <tr class="text-center">
                            <td>{{$index+1}}</td>
                            <td>{{$item->name}}</td>
                            <td>{{$item->phone}}</td>
                            <td>@forelse($item->getRoles as $rol)
                                    <span class="badge badge-primary">{{$rol->getRol->name}}</span> <br>
                                @empty
                                @endforelse
                            </td>
                            <td>{{$item->getTurno->turno ?? ''}}</td>
                            <td>
                                @foreach($item->getBranchs ?? [] as $branch)
                                    <span class="badge badge-info">{{$branch->getBranch->name}}</span><br>
                                @endforeach
                            </td>
                            <td>
                                @if(auth()->user()->hasPermissionThroughModule('usuarios','punto_venta','update') || auth()->user()->hasPermissionThroughModule('usuarios','punto_venta','show'))
                                <button type="button" class="btn btn-warning btn-sm" onClick="modal({{$item}})"><i class="fa fa-edit"></i></button>
                                @endif
                                @if(auth()->user()->hasPermissionThroughModule('usuarios','punto_venta','destroy'))
                                <a href="{{route('users.destroy', [$item->id, $status == 0 ? 1:0])}}" 
                                class="btn {{$status == 0 ? 'btn-primary':'btn-danger'}} btn-sm"><i class="fa {{$status == 0 ? 'fa-upload':'fa-trash'}}"></i></a>
                                @endif
                                @if(auth()->user()->hasPermissionThroughModule('usuarios','punto_venta','auth'))
                                <button type="button" class="btn btn-info btn-sm" onclick="btnShow({{$item}}, {{$user_branch[$item->id]}} )">
                                    <i class="fa fa-bars"></i></button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="table-warning text-center">Sin usuarios</td></tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
     @include('admin.users._modal')
  @include('admin.users._modalStoreEdit')
 </main>   
</body>
</html>