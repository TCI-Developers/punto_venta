@extends('adminlte::page')

@section('title', 'Turnos')

@section('js')
    @include('components..use.notification_success_error')

    <script>
        //funcion para abrir modal crear
        function btnShow(){
            $('#modal_create').fadeIn();
        }

        //funcion para abrir modal editar
        function btnEdit(turno){
            $('#modal_create').fadeIn();
            $('#title').html('Actualizar');
            $('#btnAddEdit').html('Actualizar');
            $('#formTurnos').attr('action', $('#formTurnos').attr('edit'));
            $('input[name=id]').val(turno.id);
            $('#turno').val(turno.turno);
            $('#description').val(turno.description);
            $('#entrada').val(turno.entrada);
            $('#salida').val(turno.salida);
        }

        //funcion para cerrar modal
        function btnCancel(turno){
            $('#modal_create').fadeOut();
            $('#title').html('Crear');
            $('#btnAddEdit').html('Crear');
            $('#formTurnos').attr('action', $('#formTurnos').attr('store'));
            $('.inputModal').val('');
        }
    </script>
@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Turnos {{$status == 0 ? 'Inhabilitados':''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <div class="form-group">
                <button type="button" class="btn btn-success" onClick="btnShow()"><img src="{{asset('icons/plus.svg')}}" width="23" alt="icono plus"> &nbsp; Crear turno</button>
                
                <a href="{{route('turnos.index', $status == 1 ? 0:1)}}" class="btn {{$status == 1 ? 'btn-light':'btn-primary'}} float-right"><img src="{{asset('icons/archive.svg')}}" width="23" alt="icono archive"> &nbsp; Turnos {{$status == 1 ? 'Inhabilitados':'Habilitados'}}</a>
            </div>

            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Turno</th>
                        <th>Horario Entrada/Salida</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($turnos as $index => $item)
                    <tr class="text-center">
                        <td>{{$index+1}}</td>
                        <td>{{$item->turno}}</td>
                        <td>{{$item->entrada}} - {{$item->salida}}</td>
                        <td>{{$item->description}}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" onClick="btnEdit({{$item}})"><img src="{{asset('icons/edit.svg')}}" alt="icono edit"></button>
                            @if($status)
                            <a href="{{route('turnos.destroy', $item->id)}}" class="btn btn-light btn-sm"><img src="{{asset('icons/trash.svg')}}" alt="icono trash"></a>
                            @else
                            <a href="{{route('turnos.enable', $item->id)}}" class="btn btn-primary btn-sm"><img src="{{asset('icons/update.svg')}}" alt="icono update"></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="table-warning text-center">Sin turnos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
  </div>

  @include('Admin.turnos._modal')
@stop