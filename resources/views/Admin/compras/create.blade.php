<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')

    <script src="{{asset('js/compras/create.js')}}"></script>
    
    <script>
        const compraStatusRoute = "{{ route('compra.status', [':id',':status']) }}";
        const compraDestroyRoute = "{{ route('compra.destroy', ':id') }}";
    </script>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
            @livewireStyles
            @livewire('compras.compra', [$compra_id, $user])
            @livewireScripts
    </main>   

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const element = document.getElementById('product_id');
            if (element) {
                new Choices(element, {
                    removeItemButton: true,
                    placeholder: true,
                    placeholderValue: 'Seleccione productos...',
                    searchEnabled: true,
                    shouldSort: false,
                });
            }
            const select = document.getElementById('product_id');
            select.addEventListener('change', function () {
                const selected = Array.from(select.selectedOptions).map(option => option.value);
                Livewire.dispatch('productsSelected', selected);
            });
        });
    </script>
</body>
</html>