<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnos</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
     <script>
        //funcion para abrir modal crear
        function btnShow(){
            $('#modal_create').show();
        }

        //funcion para abrir modal editar
        function btnEdit(turno){
            $('#modal_create').show();
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
            $('#modal_create').hide();
            $('#title').html('Crear');
            $('#btnAddEdit').html('Crear');
            $('#formTurnos').attr('action', $('#formTurnos').attr('store'));
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
            <h2>Turnos {{$status == 0 ? 'Inhabilitados':''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <div class="form-group">
                <button type="button" class="btn btn-success" onClick="btnShow()"><i class="fa fa-plus"></i> &nbsp; Crear turno</button>
                
                <a href="{{route('turnos.index', $status == 1 ? 0:1)}}" class="btn {{$status == 1 ? 'btn-light':'btn-primary'}} float-right">
                        <i class="fa fa-archive"></i> &nbsp; Turnos {{$status == 1 ? 'Inhabilitados':'Habilitados'}}</a>
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
                            <button type="button" class="btn btn-warning btn-sm" onClick="btnEdit({{$item}})">
                                <i class="fa fa-edit"></i></button>
                            @if($status)
                            <a href="{{route('turnos.destroy', $item->id)}}" class="btn btn-danger  btn-sm">
                                <i class="fa fa-trash"></i></a>
                            @else
                            <a href="{{route('turnos.enable', $item->id)}}" class="btn btn-primary btn-sm">
                                <i class="fa fa-refresh"></i></a>
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
  </main>   
</body>
</html>