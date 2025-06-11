@extends('adminlte::page')

@section('title', 'Panel de Importación DB Externa')

@section('js')
    @include('components.use.notification_success_error')
@stop

@section('content')
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
                    <a href="{{ route('import.data', 'brands') }}" class="btn btn-info"><i class="fa fa-download"></i> Marcas</a>
                </label>
                <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
                    <a href="{{ route('import.data', 'products') }}" class="btn btn-info"><i class="fa fa-download"></i> Productos</a>
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
                    <a href="{{ route('import.data', 'proveedores') }}" class="btn btn-info"><i class="fa fa-download"></i> Proveedores</a>
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
    </div>
@stop