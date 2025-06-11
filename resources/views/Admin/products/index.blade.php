<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')

    <script>
        function toggleDropdown(button) {
            const menu = button.nextElementSibling;
            menu.classList.toggle('show');
            
            // Cerrar si se hace clic fuera
            document.addEventListener('click', function handleClickOutside(event) {
                if (!button.contains(event.target) && !menu.contains(event.target)) {
                    menu.classList.remove('show');
                    document.removeEventListener('click', handleClickOutside);
                }
            });
        }
    </script>
</head>
<body>
    <main class="content">
    @include('components.use.nav-slider')
    @include('components.use.notification_success_error')
        @livewireStyles
        @livewire('products.product')
        @livewireScripts
    </main>   
</body>
</html>
