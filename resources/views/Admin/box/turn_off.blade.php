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
            $('#modal_box').show();
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
        }
    </script>

    <style>
         .modalProducts .modal-body {
            max-height: 60vh; 
            overflow-y: auto;
        }
    </style>

    @if(session('ticket'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    $('#modalTicket').show();
                });
            </script>
        @endif
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Cierre de turno</h2>
        </div>
    </div>
    @include('Admin.box._modal')
    </main>   
</body>
</html>
