@extends('adminlte::page')

@section('title', 'Despiezado de Productos')

@section('js')
    @include('components..use.notification_success_error')

    @if(count($presentations))
    <script src="{{asset('js/script_datatables.js')}}"></script>
    @endif

    <script>
        $(document).ready(function(){
            $('.selectpicker').selectpicker('refresh');
        })
        //funcion para abrir modal
        function btnAdd(){
            $('#modal_presentations').fadeIn();
        }
        
        //funcion para abrir modal edit
        function btnEdit(id){
            $('#modal_presentations').fadeIn();
            $('#title').html('Actualizar');
            $('#btnAddEdit').html('Actualizar');
            $('#formCategory').attr('action', $('#formCategory').attr('edit'));
            $('input[name=id]').val(id);

            let type = $('#'+id+' .td_type').html();
            let description = $('#'+id+' .td_description').html();

            $('#type').val(type);
            $('#description').val(description);
        }

         //funcion para cerrar modal de cancelar
         function cancelModal(){
            $('.inputModal').val('');
            $('#modal_presentations').fadeOut();
        }
    </script>
@stop

@section('content')
<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
                <h2>Presentación de Productos {{$status == 0 ? 'Inhabilitados':''}} </h2>
        </div>
        <div class="card-body">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <button type="button" class="btn btn-success text-dark" onClick="btnAdd()"
                data-toggle="tooltip" data-placement="top" title="Crear presentación"><img src="{{asset('icons/plus.svg')}}" alt="icon plus" width="23"></button>
                @if($status == 1)
                    <a href="{{route('product.indexPartProductDisabled')}}" class="btn btn-light text-dark float-right" 
                    data-toggle="tooltip" data-placement="top" title="Presentaciones Inhabilitadas"><img src="{{asset('icons/archive.svg')}}" alt="icon file" width="23">&nbsp; Inhabilitadas</a>
                @else
                    <a href="{{route('product.indexPartProduct')}}" class="btn btn-info text-dark float-right"
                    data-toggle="tooltip" data-placement="top" title="Presentaciones habilitadas"><img src="{{asset('icons/archive.svg')}}" alt="icon file" width="23">&nbsp; Habilitadas</a>
                @endif
            </div>
            <div class="table-responsive">
                <br>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Presentación</th>
                        <th>Unidad SAT</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presentations as $index => $item)
                        <tr class="text-center" id="{{$item->id}}">
                            <td>{{$index+1}}</td>
                            <td class="td_type">{{$item->type}}</td>
                            <td>{{$item->getUnidadSat->clave_unidad}} - {{$item->getUnidadSat->name}}</td>
                            <td class="td_description">{{$item->description}}</td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" onClick="btnEdit({{$item->id}})"
                                data-toggle="tooltip" data-placement="top" title="Editar"><img src="{{asset('icons/edit.svg')}}" alt="icon edit"></button>
                                @if($status == 1)
                                <a href="{{route('product.destroyPresentationProduct', [$item->id, 0])}}" class="btn btn-light btn-sm" 
                                data-toggle="tooltip" data-placement="top" title="Deshabilitar"><img src="{{asset('icons/trash.svg')}}" alt="icon trash"></a>
                                @else
                                <a href="{{route('product.destroyPresentationProduct', [$item->id, 1])}}" class="btn btn-primary btn-sm"
                                data-toggle="tooltip" data-placement="top" title="Habilitar"><img src="{{asset('icons/update.svg')}}" alt="icon update"></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                    <tr><td class="table-warning text-center" colspan="5">Sin Registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <br>
            
        </div>
  </div>
  @include('Admin.products._modal_part')
@stop