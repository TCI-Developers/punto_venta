<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs del sistema</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
     <style>
        
        tr:hover {
            background-color: #f9f9f9;
        }
        .level-INFO { color: green; }
        .level-ERROR { color: red; }
        .level-WARNING { color: orange; }
    </style>
</head>
<body>
<main class="content">
    @include('components.use.nav-slider')
    @include('components.use.notification_success_error')
     <div class="card">
        <div class="form-group card-header with-border text-center">
            <h2>Logs del sistema
                <a href="{{route('clearLogs')}}" class="btn btn-secondary float-right" data-bs-toggle="tooltip" data-bs-placement="top" title="Limpiar logs"><i class="fa fa-trash"></i></a>
            </h2>
        </div>

        <div class="card-body table-responsive">
        <table class="table col-12">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Nivel</th>
                    <th>Mensaje</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lines as $line)
                    @php
                        preg_match('/^\[(.*?)\] ([a-zA-Z\.]+): (.*)$/', $line, $matches);
                        $fecha = $matches[1] ?? '';
                        $nivel = explode('.', $matches[2] ?? '')[1] ?? '';
                        $mensaje = $matches[3] ?? $line;
                    @endphp
                    <tr>
                        <td>{{ $fecha }}</td>
                        <td class="level-{{ $nivel }}">{{ strtoupper($nivel) }}</td>
                        <td style="width:10%;">{{ $mensaje }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No hay entradas de log.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
         </div>
    </div>
</main>   
</body>
</html>
