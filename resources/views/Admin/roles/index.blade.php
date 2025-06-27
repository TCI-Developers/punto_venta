<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <style>
        .permission-module {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .permission-module-header {
            background-color: #f8f9fa;
            padding: 10px;
            font-weight: bold;
        }
        .permission-items {
            padding: 10px;
        }
        .permission-item {
            margin-bottom: 5px;
        }
    </style>
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

        //funcion para abrir modal de permisos
        function showPermissions(roleId) {
            $.get(`/roles-permissions/${roleId}`, function(data) {
                $('#permissionsModal .modal-body').html(data);
                $('#permissionsModal').modal('show');
            });
        }

        function savePermissions() {
            const roleId = $('#modalRoleId').val(); // ← esto lo toma del input oculto

            const permissions = [];
            $('input[name="permissions[]"]:checked').each(function() {
                permissions.push($(this).val());
            });

            $.ajax({
                url: `/roles-sync-permissions/${roleId}`,
                method: 'POST',
                data: {
                    permissions: permissions,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {                    
                    $('#permissionsModal').modal('hide');
                    Swal.fire('Permisos actualizados correctamente', '', 'success');
                },
                error: function(xhr) {
                    Swal.fire('Error al actualizar permisos', '', 'error');
                }
            });
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
                    @if(auth()->user()->hasPermissionThroughModule('roles','punto_venta','create'))
                    <button type="button" class="btn btn-success" onClick="btnShow()">
                        <i class="fa fa-plus"></i> &nbsp; Crear Rol
                    </button>
                    @endif
                    @if(auth()->user()->hasPermissionThroughModule('roles','punto_venta','destroy'))
                    <a href="{{route('roles.index', $status == 1 ? 0:1)}}" class="btn {{$status == 1 ? 'btn-light':'btn-primary'}} float-right">
                        <i class="fa fa-archive"></i> &nbsp; Roles {{$status == 1 ? 'Inhabilitados':'Habilitados'}}
                    </a>
                    @endif
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
                                @if(auth()->user()->hasPermissionThroughModule('roles','punto_venta','auth') || Auth::User()->hasRole('root'))
                                <button type="button" class="btn btn-info btn-sm" onClick="showPermissions({{$item->id}})">
                                    <i class="fa fa-key"></i> Permisos
                                </button>
                                @endif
                                @if(auth()->user()->hasPermissionThroughModule('roles','punto_venta','update') || auth()->user()->hasPermissionThroughModule('roles','punto_venta','show'))
                                <button type="button" class="btn btn-warning btn-sm" onClick="btnEdit({{$item}})">
                                    <i class="fa fa-edit"></i>
                                </button>
                                @endif

                                @if(auth()->user()->hasPermissionThroughModule('roles','punto_venta','destroy'))
                                    @if($status)
                                    <a href="{{route('roles.destroy', $item->id)}}" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    @else
                                    <a href="{{route('roles.enable', $item->id)}}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-refresh"></i>
                                    </a>
                                    @endif
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
        
        <!-- Modal para permisos -->
        <div class="modal fade" id="permissionsModal" tabindex="-1" role="dialog" aria-labelledby="permissionsModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="permissionsModalLabel">Administrar Permisos</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Contenido cargado por AJAX -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="savePermissions()">Guardar Cambios</button>
                    </div>
                </div>
            </div>
        </div>
    </main>   
</body>
</html>