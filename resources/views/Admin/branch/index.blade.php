@extends('adminlte::page')

@section('title', 'Sucursal')

@section('js')
    @include('components..use.notification_success_error')

    @if ($errors->any())
    <script>
        $(document).ready(function() {
            Swal.fire({
                icon: 'info',
                title: 'Validación de campos',
                html: `
                    <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                `
            });
        });
    </script>
    @endif

    <script>
        //funcion para habilitar boton editar
        function edit(){
            $('#btnEdit').fadeOut(function(){
                $('#btnSubmit').fadeIn();
                $('#btnCancel').fadeIn();
            });
            $('.inputs').attr('readonly', false);
            $('.selectpicker').attr('disabled', false).selectpicker('refresh');
        }
        //funcion para habilitar boton editar
        function cancel(){
            $('#btnSubmit').fadeOut(function(){
                $('#btnEdit').fadeIn();
            });
            $('#btnCancel').fadeOut();
            $('.inputs').attr('readonly', true);
            $('.selectpicker').attr('disabled', true).selectpicker('refresh');
        }

        //funcion para importar productos y se relacionen a la sucursal
        function importProducts(branch_id){
            console.log('*', branch_id);
            
            Swal.fire({
                title: "¿Deseas importar productos a esta sucursal?",
                showCancelButton: true,
                confirmButtonText: "Aceptar",
                denyButtonText: `Cancelar`
            }).then((result) => {
                if (result.isConfirmed) {
                    mostrarCargando();
                    let productImportUrl = "{{ route('import.products', ':id') }}".replace(':id', branch_id);
                    window.location.href = productImportUrl;
                }
            });
        }

        function mostrarCargando() {
            Swal.fire({
                title: 'Cargando...',
                text: 'Por favor, espera mientras se completa el proceso.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); // Muestra la animación de carga
                }
            });
        }
    </script>
@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Sucursal</h2>
        </div>
        <div class="col-12 mt-2">
            <a href="{{route('branchs.index')}}" class="btn btn-success"><i class="fa fa-arrow-left"></i></a>
            @isset($branch)
            <button type="button" class="btn btn-primary float-right" onclick="importProducts({{$branch->id}})"><i class="fa fa-download"></i> Importar Productos</button>
            @endisset
        </div>
        <div class="card-body">
            <form action="{{route('branchs.store', isset($branch) ? $branch->id:'')}}" method="post">
            @csrf
            <input type="hidden" name="status" value="{{$status}}">
            <div class="row">
                <label for="name" class="col-lg-12 col-md-12 col-sm-12">Nombre Sucursal*
                    <input type="text" name="name" id="name" class="form-control inputs" placeholder="Nombre Sucursal" value="{{$branch->name ?? ''}}" {{$status ? 'readonly':''}} required>
                </label>
                <label for="razon_social" class="col-lg-12 col-md-12 col-sm-12">Razón Social
                    <input type="text" name="razon_social" id="razon_social" class="form-control inputs" placeholder="Razón Social" value="{{$branch->razon_social ?? ''}}" {{$status ? 'readonly':''}}>
                </label>
                <label for="address" class="col-lg-12 col-md-12 col-sm-12">Dirección
                    <input type="text" name="address" id="address" class="form-control inputs" placeholder="Dirección" {{$status ? 'readonly':''}} value="{{$branch->address ?? ''}}">
                </label>
                <label for="rfc" class="col-lg-4 col-md-4 col-sm-12">RFC
                    <input type="text" name="rfc" id="rfc" class="form-control inputs" placeholder="RFC" {{$status ? 'readonly':''}} value="{{$branch->rfc ?? ''}}">
                </label>
                <label for="phone" class="col-lg-4 col-md-4 col-sm-12">Telefono
                    <input type="phone" name="phone" id="phone" class="form-control inputs" placeholder="Telefono" {{$status ? 'readonly':''}} value="{{$branch->phone ?? ''}}" >
                </label>
                <label for="address" class="col-lg-4 col-md-4 col-sm-12">Usuarios
                    <select name="user_id[]" id="user_id" class="form-control selectpicker" title="Usuarios" multiple {{$status ? 'disabled':''}}>
                        @foreach($users as $item)
                            @if(isset($users_exist_in_branch) && $users_exist_in_branch !== 'false')
                            @foreach($users_exist_in_branch as $user_exist)
                            <option value="{{$item->id}}" {{$item->id == $user_exist->user_id ? 'selected':'' }}>{{$item->name}}</option>
                            @endforeach
                            @else
                            <option value="{{$item->id}}">{{$item->name}}</option>
                            @endif
                        @endforeach
                    </select>
                </label>
            </div>
            <div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 text-right">
                    <button type="submit" class="btn btn-primary text_color" id="btnSubmit" style="{{$status ? 'display:none;':''}}"><img class="icon_img" src="{{asset('icons/save.svg')}}" alt="icon save">{{$status ? 'Actualizar':'Guardar'}}</button>
                    @if(isset($branch))
                    <button type="button" class="btn btn-success text_color" id="btnEdit" onClick="edit()"><img class="icon_img" src="{{asset('icons/update.svg')}}" alt="icon update"> Habilitar edición</button>
                    <button type="button" class="btn btn-light text_color" id="btnCancel" onClick="cancel()" style="display:none;"><img class="icon_img" src="{{asset('icons/cancel.svg')}}" alt="icon cancel"> Cancelar</button>
                    @endif
                </div>
            </div>
            </form>
        </div>
  </div>
@stop