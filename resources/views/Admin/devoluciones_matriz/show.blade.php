<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devoluciones</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.devolver-todo-checkbox').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    let row = this.closest('tr');
                    let cantidadInput = row.querySelector('.cantidad-input');
                    let max = this.getAttribute('data-max');

                    if (this.checked) {
                        cantidadInput.value = max;
                    } else {
                        cantidadInput.value = 0;
                    }
                });
            });
        });
    </script>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
        
        @livewireStyles
        @livewire('devolucioncompra.devolucionshowmatriz', [$id])
        @livewireScripts
    </main>   
</body>
</html>