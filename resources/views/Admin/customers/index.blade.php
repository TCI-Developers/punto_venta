<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')

    <script>
        //funcion para mostrar modal
        function showModal(){
            $('#modal_customer').show();
        }

        //funcion para cancelar en modal
        function btnCancel(){
            $('#modal_customer').hide();
            $('.inputModal').val('');
            $('.title').html('Agregar');
            $('input[name=id]').val('').attr('disabled', true);
        }

        //funcion para abrir modal edit
        window.addEventListener('showModalEdit', event => {
            $('#modal_customer').show();
            $('.title').html('Actualizar');
            $('input[name=id]').val(event.detail[0].customer.id).attr('disabled', false);
            $('#name').val(event.detail[0].customer.name);
            $('#razon_social').val(event.detail[0].customer.razon_social);
            $('#rfc').val(event.detail[0].customer.rfc);
            $('#postal_code').val(event.detail[0].customer.postal_code);
            $('#regimen_fiscal').val(event.detail[0].customer.regimen_fiscal);
        })
    </script>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
        
        @livewireStyles
        @livewire('customers.customer')
        @livewireScripts
    </main>   
</body>
</html>