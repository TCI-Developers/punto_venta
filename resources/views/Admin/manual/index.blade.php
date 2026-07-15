<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario — POS TCI</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <style>
        .manual-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 24px 20px;
            text-align: center;
            transition: box-shadow .2s, transform .15s;
            height: 100%;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .manual-card:hover { text-decoration: none; color: inherit; }
        .manual-card.disponible:hover {
            box-shadow: 0 4px 18px rgba(0,0,0,.12);
            transform: translateY(-3px);
        }
        .manual-card .icono {
            font-size: 2.4rem;
            margin-bottom: 12px;
        }
        .manual-card.disponible .icono { color: #28a745; }
        .manual-card.no-disponible { opacity: .5; cursor: default; }
        .manual-card.no-disponible .icono { color: #adb5bd; }
        .badge-pronto {
            display: inline-block;
            font-size: .7rem;
            background: #e9ecef;
            color: #6c757d;
            border-radius: 20px;
            padding: 2px 10px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')

        <div class="card card-primary">
            <div class="card-header with-border text-center">
                <h2>
                    <i class="fa fa-book"></i> Manual de Usuario
                </h2>
                <p class="text-muted mb-0" style="font-size:.95rem;">
                    Selecciona el módulo que deseas consultar.
                </p>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($modulos as $m)
                    <div class="col-md-4 col-sm-6 mb-4">
                        @if($m['disponible'])
                        <a href="{{ route('manual.show', $m['slug']) }}" class="manual-card disponible">
                            <div class="icono"><i class="fa {{ $m['icono'] }}"></i></div>
                            <h5 class="mb-1">{{ $m['titulo'] }}</h5>
                            <p class="text-muted mb-0" style="font-size:.85rem;">{{ $m['descripcion'] }}</p>
                        </a>
                        @else
                        <div class="manual-card no-disponible">
                            <div class="icono"><i class="fa {{ $m['icono'] }}"></i></div>
                            <h5 class="mb-1">{{ $m['titulo'] }}</h5>
                            <p class="text-muted mb-0" style="font-size:.85rem;">{{ $m['descripcion'] }}</p>
                            <span class="badge-pronto">Próximamente</span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>
</body>
</html>
