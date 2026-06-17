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
        .perm-card { border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 12px; overflow: hidden; }
        .perm-card-header { background: #f8f9fa; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #dee2e6; }
        .perm-card-header .mod-title { font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; }
        .perm-card-body { padding: 10px 50px; display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
        .perm-item { display: flex; align-items: center; gap: 5px; }
        .perm-item label { margin: 0; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; gap: 4px; white-space: nowrap; }
        .perm-help { cursor: pointer; color: #6c757d; font-size: 0.75rem; border: 1px solid #ced4da; border-radius: 50%; width: 16px; height: 16px; display: inline-flex; align-items: center; justify-content: center; line-height: 1; flex-shrink: 0; }
        .perm-help:hover { background: #6c757d; color: #fff; border-color: #6c757d; }
        .select-all-label { font-size: 0.8rem; color: #6c757d; display: flex; align-items: center; gap: 5px; cursor: pointer; margin-bottom: 0; }
        .perm-help { position: relative; cursor: default; }
        .perm-help::after { content: attr(data-tooltip); position: absolute; bottom: 130%; left: 50%; transform: translateX(-50%); background: #333; color: #fff; font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; white-space: normal; width: 200px; text-align: left; opacity: 0; pointer-events: none; transition: opacity 0.2s; z-index: 9999; line-height: 1.4; }
        .perm-help:hover::after { opacity: 1; }
        .perm-item-disabled { opacity: 0.4; cursor: not-allowed; }
        .perm-item-disabled * { pointer-events: none; }
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
            <div class="modal-dialog modal-xl" role="document">
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