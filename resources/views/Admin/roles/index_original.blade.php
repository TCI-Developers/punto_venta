<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
     <script>
        //funcion para abrir modal crear
        function btnShow(){
            $('#modal_create').show();
        }

        //funcion para abrir modal editar
        function btnEdit(rol){
            $('#modal_create').show();
            $('#title').html('Actualizar');
            $('#btnAddEdit').html('Actualizar');
            $('#formRoles').attr('action', $('#formRoles').attr('edit'));
            $('input[name=id]').val(rol.id);
            $('#name').val(rol.name);
            $('#description').val(rol.description);
        }

        //funcion para cerrar modal
        function btnCancel(){
            $('#modal_create').hide();
            $('#title').html('Crear');
            $('#btnAddEdit').html('Crear');
            $('#formRoles').attr('action', $('#formRoles').attr('store'));
            $('.inputModal').val('');
        }
    </script>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Roles {{$status == 0 ? 'Inhabilitados':''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <div class="form-group">
                <button type="button" class="btn btn-success" onClick="btnShow()"><i class="fa fa-plus"></i> &nbsp; Crear Rol</button>
                
                <a href="{{route('roles.index', $status == 1 ? 0:1)}}" class="btn {{$status == 1 ? 'btn-light':'btn-primary'}} float-right">
                    <i class="fa fa-archive"></i> &nbsp; Roles {{$status == 1 ? 'Inhabilitados':'Habilitados'}}</a>
            </div>

            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Rol</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $index => $item)
                    <tr class="text-center">
                        <td>{{$index+1}}</td>
                        <td>{{$item->name}}</td>
                        <td>{{$item->description}}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" onClick="btnEdit({{$item}})">
                                <i class="fa fa-edit"></i></button>
                            @if($status)
                            <a href="{{route('roles.destroy', $item->id)}}" class="btn btn-danger btn-sm">
                                <i class="fa fa-trash"></i></a>
                            @else
                            <a href="{{route('roles.enable', $item->id)}}" class="btn btn-primary btn-sm">
                                <i class="fa fa-refresh"></i></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="table-warning text-center">Sin Roles.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
  </div>

  @include('Admin.roles._modal')
  </main>   
</body>
</html>