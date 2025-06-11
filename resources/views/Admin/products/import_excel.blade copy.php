@extends('adminlte::page')

@section('title', 'Carga de archivo excel')

@section('css')
@stop

@section('js')
    @include('components..use.notification_success_error')
@stop

@section('content')
<div class="card">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h4 class="text-center mb-0">Cargar Archivo Excel</h4>
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
                    <small class="text-muted">Asegúrese de que el archivo tenga las columnas correctas (Código de Barras y Precio).</small>
                </div>

                <div class="col-lg-12 text-right">
                    <button type="submit" class="btn btn-success">Subir y Procesar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop