@extends('adminlte::page')

@section('title', 'Crear promociones')

@section('js')
    @include('components.use.notification_success_error')
@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>{{$promotion ? 'Actualizar':'Nueva'}} Promocion</h2>
        </div>
        @if($promotion)
        <form action="{{route('promos.update', $promotion->id)}}" method="post">
        @else
        <form action="{{route('promos.store')}}" method="post">
        @endif
            @csrf
            <div class="card-body table-responsive">
                <div class="row form-group">
                <label for="branch_id" class="col-lg-6 col-md-6 col-sm-6">Sucursal
                        <select name="branch_id" id="branch_id" class="form-control selectpicker" title="Sucursal">
                            @foreach($branchs as $item)
                                <option value="{{$item->id}}" {{isset($promotion) && $promotion->branch_id == $item->id ? 'selected':''}}>{{$item->name}} - {{$item->address}}</option>
                            @endforeach
                        </select>
                </label>
                <label for="description" class="col-lg-6 col-md-6 col-sm-6">Descripción
                        <input type="text" class="form-control" name="description" id="description" placeholder="Descripción" value="{{$promotion->description ?? ''}}">
                </label>
                <label for="cantidad_producto" class="col-lg-6 col-md-6 col-sm-6">Cantidad de producto (Entrega)
                        <input type="number" class="form-control" name="cantidad_producto" id="cantidad_producto" placeholder="0" value="{{$promotion->cantidad_producto ?? ''}}">
                </label>
                <label for="cantidad_productos_a_pagar" class="col-lg-6 col-md-6 col-sm-6">Cantidad de producto a pagar
                        <input type="number" class="form-control" name="cantidad_productos_a_pagar" id="cantidad_productos_a_pagar" value="{{$promotion->cantidad_productos_a_pagar ?? ''}}" placeholder="0">
                </label>
                <label for="vigencia_cantidad" class="col-lg-6 col-md-6 col-sm-6">Vigencia por cantidad
                        <input type="number" class="form-control" name="vigencia_cantidad" id="vigencia_cantidad" value="{{$promotion->vigencia_cantidad ?? ''}}" placeholder="0">
                </label>
                <label for="vigencia_fecha" class="col-lg-6 col-md-6 col-sm-6">Vigencia por fecha
                        <input type="date" class="form-control" name="vigencia_fecha" id="vigencia_fecha" value="{{$promotion->vigencia_fecha ?? date('Y-m-d')}}">
                </label>
                </div>
            </div>
            <div class="card-footer">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-check"></i> {{$promotion ? 'Actualizar':'Crear'}}</button> 
                    <a href="{{route('promos.index')}}" class="btn btn-danger float-right mr-2"><i class="fa fa-times"></i> Cancelar</a>
                </div>
                </div>
            </div>
        </form>
  </div>

@stop