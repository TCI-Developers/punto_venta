<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cierres de turno</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <script src="{{asset('js/box/index.js')}}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if($mostrarTicket ?? false)
                // Turno recién cerrado: mostrar ticket directamente
                $('#modalTicket').show();
                if(window.loadTicketBox) window.loadTicketBox();
            @else
                // Turno abierto: mostrar formulario de conteo
                $('#modal_box').show();
            @endif
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

        //funcion para mostrar el ticket del usuario
        function ticket(){
            $('#modalTicket').show();
            if(window.loadTicketBox) window.loadTicketBox();
        }
    </script>

    <style>
         .modalProducts .modal-body {
            max-height: 60vh; 
            overflow-y: auto;
        }
    </style>

    {{-- El ticket se controla desde el DOMContentLoaded principal arriba --}}
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h3>Cierre de turno</h3>
        </div>
    </div>
    @include('Admin.box._modal')
    </main>   
</body>
</html>
