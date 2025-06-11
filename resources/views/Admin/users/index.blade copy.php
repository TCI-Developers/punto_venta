@extends('adminlte::page')

@section('title', 'Usuarios')

@section('js')
    @include('components.use.notification_success_error')
    
    <script>
        //funcion para abrir modal crear
        function btnShow(user, user_branch){
            $('#modal_create').fadeIn();
            $('input[name=id]').val(user.id);

            let val = [];
            $.each(user.get_roles, function(index, value){
                val.push(value.role_id.toString());
            });
            
            let branch = [];
            $.each(user_branch, function(index, value){
                branch.push(value.branch_id.toString());                
            });

            $('#role_id').val(val).selectpicker('refresh');
            $('#turno_id').val(user.turno_id).selectpicker('refresh');
            $('#branch_id').val(branch).selectpicker('refresh');
        }

        //funcion para cerrar modal
        function btnCancel(){
            $('#modal_create').fadeOut();
            $('.selectpicker').val('').selectpicker();
        }

        // switch de la contraseña
        $('#switchPass').on('click', function(){
            if($(this).prop('checked')){
                $('#confirmedPass').attr('readonly', false);
                $('#password').attr('readonly', false);
                $('#btnUpdate').attr('disabled', true);
            }else{
                $('#confirmedPass').attr('readonly', true);
                $('#password').attr('readonly', true);
                $('#btnUpdate').attr('disabled', false);
            }
        });

        // Campo confirmar contraseña, para comparar si son iguales
        $('#confirmedPass').on('change', function(){
            let pass = $('#password').val();
            let confirmedPassword = $(this).val();

            if(pass != confirmedPassword){
                Swal.fire(
                    'Las contraseñas no coinciden.',
                    '',
                    'info'
                )
                $(this).val('');
                $('#password').val('');
            }else{
                $('#btnUpdate').attr('disabled', false);
            }
        });

        //funcion para abrir modal edita usuario
        function modal(user){
            $('.inputs').val('');
            if(user !== 'null'){
                $('#formUser').attr('action', $('#formUser').attr('edit'));
                $('.title').html('Actualizar');
                $('input[name=user_id]').val(user.id);
                $('#name').val(user.name);
                $('#email').val(user.email);
                $('#phone').val(user.phone);
                $('#switch_pass').show();
                $('.pass').attr('required', false).attr('readonly', true);
                $('#users').modal('show');
            }else{
                $('#formUser').attr('action', $('#formUser').attr('store')); 
                $('#switch_pass').hide();
                $('#users').modal('hide');
            }
        }
    </script>
@stop

@section('content')
    <div class="card {{$status == 0 ? 'card-secondary':'card-primary'}}">
        <div class="form-group card-header with-border text-center">
            <h2>Usuarios {{$status == 0 ? 'Inhabilidatos':''}}</h2>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="card-header">
                <a class="btn btn-primary" data-toggle="modal" data-target="#users" style="cursor:pointer;"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Nuevo usuario"><i class="fa fa-plus"></i></a>

                <a href="{{route('users.index', $status == 0 ? 1:0)}}" class="btn {{$status == 0 ? 'btn-success':'btn-secondary'}} float-right" data-bs-toggle="tooltip" data-bs-placement="top" 
                    title="Usuarios {{$status == 0 ? 'Habilitados':'Inhabilitados'}}"><i class="fa fa-folder"></i></a>
            </div>
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
                        <td>{{$item->getBranch->name ?? ''}}</td>
                        <td>
                            @if(!Auth::User()->hasRole('root'))
                            <button type="button" class="btn btn-warning btn-sm" onClick="modal({{$item}})"><i class="fa fa-edit"></i></button>
                            <a href="{{route('users.destroy', [$item->id, $status == 0 ? 1:0])}}" 
                                class="btn {{$status == 0 ? 'btn-primary':'btn-danger'}} btn-sm"><i class="fa {{$status == 0 ? 'fa-upload':'fa-trash'}}"></i></a>
                            @endif

                            <button type="button" class="btn btn-info btn-sm" onClick="btnShow({{$item}}, {{$user_branch[$item->id]}} )"><img src="{{asset('icons/list.svg')}}" alt="icon list"></button>
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
@stop