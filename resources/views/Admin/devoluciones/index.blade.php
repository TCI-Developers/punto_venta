@extends('adminlte::page')

@section('title', 'Devoluciones')

@section('js')
    @include('components..use.notification_success_error')

@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Devoluciones {{$status == 0 ? 'Inhabilitadas':''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <div class="form-group">
                <a class="btn btn-primary" href="{{route('devoluciones.createMatriz')}}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Nueva devolución"><i class="fa fa-plus"> Matriz</i></a>

                <a class="btn btn-success" href="{{route('devoluciones.showListadoVentas')}}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Nueva devolución"><i class="fa fa-plus"> Venta</i></a>
                
                <a href="{{route('devoluciones.index', $status == 0 ? 1:0)}}" class="btn {{$status == 0 ? 'btn-success':'btn-secondary'}} float-right" data-bs-toggle="tooltip" data-bs-placement="top" 
                    title="Usuarios {{$status == 0 ? 'Habilitados':'Inhabilitados'}}"><i class="fa fa-folder"></i></a>
            </div>

            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Folio Venta</th>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Descripción</th>
                        <th>Descuento</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devoluciones as $index => $item)
                       <tr>
                            <td class="text-center">{{$item->getSale->folio}}</td>
                           <td class="text-center">{{date('d/m/Y', strtotime($item->fecha_devolucion))}}</td>
                           <td class="text-center">{{$item->cantidad}}</td>
                           <td>{{$item->description}}</td>
                           <td class="text-center">${{number_format($item->total_descuentos, 2)}}</td>
                           <td class="text-center">${{number_format(($item->total_devolucion - $item->total_descuentos), 2)}}</td>
                            <td class="text-center">
                                <a href="{{route('devoluciones.showDevSale', $item->id)}}" class="btn btn-info"><i class="fa fa-eye"></i></a>
                            </td>
                       </tr>
                    @empty
                    <tr><td colspan="7" class="table-warning text-center">Sin devoluciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
  </div>

@stop