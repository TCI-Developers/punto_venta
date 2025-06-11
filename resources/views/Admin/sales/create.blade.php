<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')

    <script src="{{asset('js/sales/create.js')}}"></script>
    <style>
        .table-dev{
            background-color: #00000050 !important;
        }

        .modalProducts .modal-body {
            max-height: 60vh; 
            overflow-y: auto;
        }
    </style>

</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
        @livewireStyles
        @livewire('sales.sale', [$type, $id]) 
        @livewireScripts
   </main>   
</body>
</html>