<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
</head>
<body>
    <main class="content">
    @include('components.use.nav-slider')
    @include('components.use.notification_success_error')
<div class="card">
    <div class="card shadow-lg">
        <div class="card-header">
            <h4 class="text-center mb-0">
                <a href="{{route('product.index')}}" class="btn btn-success float-left btn-sm"
                    data-toggle="tooltip" data-placement="top" title="Regresar"><i class="fa fa-arrow-left"></i></a> 
                Cargar Archivo Excel
            </h4>
        </div>
        <div class="card-body">

            <!-- Mensaje de éxito -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Mensaje de error -->
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulario -->
            <form action="{{ route('product.uploadExcel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="col-lg-12 mb-3">
                    <label for="excel_file" class="form-label">Seleccione el archivo Excel</label>
                    <input type="file" class="form-control" name="excel_file" id="excel_file" required>
                    <small class="text-muted">Asegúrese de que el archivo tenga las columnas correctas (Codigo de Producto, Stock y Código de Barras).</small>
                </div>

                <div class="col-lg-12 text-right">
                    <button type="submit" class="btn btn-success">Subir y Procesar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('confirmExcelPrice'))
    @php $pendiente = session('confirmExcelPrice'); @endphp
    <!-- El archivo trae una columna "Precio" (precio real de despiece) -- se pregunta antes
         de importar si se debe usar tal cual en vez del calculo producto->precio_despiece/cantidad. -->
    <form action="{{ route('product.confirmUploadExcel') }}" method="POST" id="formConfirmExcelPrice" style="display:none;">
        @csrf
        <input type="hidden" name="temp_path" value="{{ $pendiente['path'] }}">
        <input type="hidden" name="reader" value="{{ $pendiente['reader'] }}">
        <input type="hidden" name="use_excel_price" id="use_excel_price_confirm" value="0">
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'question',
                title: 'Se detectó una columna de Precio',
                html: 'El archivo <b>{{ $pendiente['nombre_original'] }}</b> trae una columna de Precio.<br>¿Deseas usar esos precios como precio final de las presentaciones de despiece?',
                showCancelButton: true,
                confirmButtonText: 'Sí, usarlos',
                cancelButtonText: 'No, calcular como siempre',
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-secondary'
                },
            }).then((result) => {
                document.getElementById('use_excel_price_confirm').value = result.isConfirmed ? '1' : '0';
                document.getElementById('formConfirmExcelPrice').submit();
            });
        });
    </script>
@endif
 </main>
</body>
</html>