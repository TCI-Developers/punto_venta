<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <link rel="stylesheet" href="{{asset('css/style_load_view.css')}}">
</head>

<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
        @livewireStyles
        @livewire('reports.report')
        @livewireScripts

        <!-- ===== LOADER ===== -->
        <div class="loader" id="loader" role="status" aria-live="polite" aria-hidden="true">
            <div class="loader__backdrop" aria-hidden="true"></div>
            <div class="loader__panel" role="group" aria-label="Cargando">
            <!-- Spinner SVG -->
            <svg class="loader__spinner" viewBox="0 0 50 50" aria-hidden="true">
                <defs>
                <linearGradient id="grad" x1="0" x2="1">
                    <stop offset="0%" stop-color="currentColor"/>
                    <stop offset="100%" stop-color="currentColor" />
                </linearGradient>
                </defs>
                <circle class="track" cx="25" cy="25" r="20" />
                <circle class="indicator" cx="25" cy="25" r="20" />
            </svg>

            <div class="loader__label" id="loader-label">Cargando…</div>

            <div class="loader__progress" aria-label="Progreso">
                <div class="loader__bar" id="loader-bar" style="width:0%"></div>
            </div>

            <div class="loader__percent" id="loader-percent">0%</div>
            </div>
        </div>

       
       <script src="{{asset('js/load_view.js')}}"></script>
    </main>
</body>
</html>