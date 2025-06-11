@extends('adminlte::page')

@section('title')
    {{isset($proveedor) ? 'Actualizar proveedor':'Nuevo proveedor'}}
@stop

@section('js')
    @include('components..use.notification_success_error')
@stop

@section('content')
<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>{{isset($proveedor) ? 'Actualizar proveedor':'Nuevo proveedor'}}</h2>
        </div>

        <form action="{{route('proveedor.store', isset($proveedor) ? $proveedor->id:null )}}" method="post">
            @csrf
            <div class="card-body table-responsive">
                <div class="form-group">
                    <a href="{{route('proveedor.index')}}" class="btn btn-success"><i class="fa fa-arrow-left"></i></a>
                    <hr>
                </div>

                <div class="row form-group">
                    <label for="name" class="col-lg-4 col-sm-12">Nombre
                        <input type="text" class="form-control" name="name" id="name" value="{{isset($proveedor) ? $proveedor->name:''}}">
                    </label>
                    <label for="code_proveedor" class="col-lg-4 col-sm-12">Codigo
                        <input type="text" class="form-control" name="code_proveedor" id="code_proveedor" value="{{isset($proveedor) ? $proveedor->code_proveedor:''}}">
                    </label>
                    <label for="rfc" class="col-lg-4 col-sm-12">RFC
                        <input type="text" class="form-control" name="rfc" id="rfc" value="{{isset($proveedor) ? $proveedor->rfc:''}}">
                    </label>
                    <label for="phone" class="col-lg-4 col-sm-12">Telefono
                        <input type="tel" class="form-control" name="phone" id="phone" placeholder="ej: 4521234567" value="{{isset($proveedor) ? $proveedor->phone:''}}">
                    </label>

                    <label for="contacto" class="col-lg-4 col-sm-12">Contacto
                        <input type="text" class="form-control" name="contacto" id="contacto" value="{{isset($proveedor) ? $proveedor->contacto:''}}">
                    </label>
                    <label for="email" class="col-lg-4 col-sm-12">Email
                        <input type="email" class="form-control" name="email" id="email" placeholder="ej: email@email.com" value="{{isset($proveedor) ? $proveedor->email:''}}">
                    </label>
                    <label for="address" class="col-lg-4 col-sm-12">Dirección
                        <input type="text" class="form-control" name="address" id="address" value="{{isset($proveedor) ? $proveedor->address:''}}">
                    </label>
                    <label for="credit_days" class="col-lg-4 col-sm-12">Dias de credito
                        <input type="number" class="form-control" name="credit_days" id="credit_days" placeholder="0.00" value="{{isset($proveedor) ? $proveedor->credit_days:''}}">
                    </label>

                    <label for="credit" class="col-lg-4 col-sm-12">Credito
                        <input type="number" class="form-control" name="credit" id="credit" placeholder="0.00" value="{{isset($proveedor) ? $proveedor->credit:''}}" disabled>
                    </label>
                    <label for="saldo" class="col-lg-4 col-sm-12">Saldo
                        <input type="number" class="form-control" name="saldo" id="saldo" placeholder="0.00" value="{{isset($proveedor) ? $proveedor->saldo:''}}" disabled>
                    </label>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{isset($proveedor) ? 'Actualizar':'Guardar'}}</button>
            </div>
        </form>
</div>
@stop