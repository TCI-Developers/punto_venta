@extends('adminlte::page')

@section('title', 'Empresa')

@section('css')
    <style>
        .uppercase{
            text-transform: uppercase;
        }
    </style>
@stop

@section('js')
    @include('components..use.notification_success_error')
@stop

@section('content')
<div class="card card-primary">
    <div class="form-group card-header with-border text-center">
        <h2>Empresa</h2>
    </div>
    <div class="card-body">
        <form action="{{route('admin.empresaUpdate')}}" method="post">
            @csrf
            <div class="container">
                <label for="razon_social" class="col-12">RAZÓN SOCIAL*
                    <input type="text" class="form-control uppercase" name="razon_social" id="razon_social" value="{{$empresa->razon_social ?? ''}}" required>
                </label>
                <label for="name" class="col-12">NOMBRE*
                    <input type="text" class="form-control uppercase" name="name" id="name" value="{{$empresa->name ?? ''}}" required>
                </label>
                <label for="rfc" class="col-12">RFC*
                    <input type="text" class="form-control" name="rfc" id="rfc" value="{{$empresa->rfc ?? ''}}" required>
                </label>
                <label for="address" class="col-12">DIRECCIÓN*
                    <input type="text" class="form-control" name="address" id="address" value="{{$empresa->address ?? ''}}" required>
                </label>

                @if(Auth::User()->hasAnyRole('root', 'admin'))
                <div class="form-group text-right mt-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Guardar</button>
                </div>
                @endif
            </div>
        </form>
    </div>
    
    @if(Auth::User()->hasAnyrole(['root', 'admin']))
    <div class="card-body">
        <hr>
            <h3 class="text-center text-bold">Importación</h3>
        @include('Admin.root.importacion_DBExt_DBLocal')
    </div>
    @endif
</div>
@stop