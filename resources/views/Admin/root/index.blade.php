<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importación QuickBAse a DB Externa</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Importación QuickBAse a DB Externa</h2>
        </div>

        <div class="card-body">
            <div class="row">
                <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
                    <a href="{{ route('import.data', 'users') }}" class="btn btn-info"><i class="fa fa-download"></i> Usuarios</a>
                </label>
                <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
                    <a href="{{ route('import.data', 'empresa_details')}}" class="btn btn-info"><i class="fa fa-download"></i> Datos empresa</a>
                </label>
                <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
                    <a href="{{ route('import.data', 'drivers') }}" class="btn btn-info"><i class="fa fa-download"></i> Choferes</a>
                </label>
                <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
                    <a href="{{ route('import.data', 'payment_methods') }}" class="btn btn-info"><i class="fa fa-download"></i> Metodos de pago</a>
                </label>
                <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
                    <a href="{{ route('import.data', 'unidades_sat') }}" class="btn btn-info"><i class="fa fa-download"></i> Unidades SAT</a>
                </label>
                <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
                    <a href="{{ route('import.data', 'branchs') }}" class="btn btn-info"><i class="fa fa-download"></i> Sucursales</a>
                </label>
            </div>
        </div>
        
        <br>
        <div class="form-group card-header with-border text-center">
            <h2>Importación DB Externa a DB Local</h2>
        </div>

        @include('Admin.root.importacion_DBExt_DBLocal')

        <br>
        <div class="form-group card-header with-border text-center">
            <h2>Importación Matriz &rarr; DB Local</h2>
        </div>

        <div class="card-body">
            <div class="row">
                <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
                    <a id="btnSyncMatriz" href="{{ route('import.catalogoMatriz') }}" class="btn btn-success"><i class="fa fa-download"></i> Sincronizar catálogo desde Matriz</a>
                </label>
            </div>
        </div>
    </div>

    <div id="loadingSyncMatriz" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:9999; flex-direction:column; align-items:center; justify-content:center; color:#fff;">
        <div class="spinner-border text-light mb-3" style="width:3rem; height:3rem;" role="status"></div>
        <p class="mb-0" style="font-size:1.1rem;">Sincronizando catálogo desde Matriz...</p>
        <small class="text-white-50">Esto puede tardar unos minutos, por favor espera.</small>
    </div>
    <script>
    document.getElementById('btnSyncMatriz').addEventListener('click', function() {
        this.classList.add('disabled');
        this.style.pointerEvents = 'none';
        document.getElementById('loadingSyncMatriz').style.display = 'flex';
    });
    </script>
   </main>
</body>
</html>