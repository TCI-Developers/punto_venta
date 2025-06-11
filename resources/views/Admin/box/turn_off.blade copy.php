@extends('adminlte::page')

@section('title', 'Cierre Turno')

@section('js')
    @include('components..use.notification_success_error')

    <script>
        $(document).ready(function(){
            $('#modal_box').fadeIn();
        })

        //fucion para el conteo de billetes y monedas
        function totalTicketsCoins($type){
            if($type == 'tickets'){
                let total = 0;
                $.each($('.tickets'), function(index, item){
                    total += item.value * $(item).attr('valor');
                });
                $('#totalTickets').html('Total en billetes: $'+total);
            }else{
                let total = 0;
                $.each($('.coins'), function(index, item){
                    total += item.value * $(item).attr('valor');
                });
                $('#totalCoins').html('Total en monedas: $'+total);
            }
        }
    </script>
@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Cierre de turno</h2>
        </div>
    </div>
    @include('Admin.box._modal')
@stop