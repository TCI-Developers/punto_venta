@extends('adminlte::page')

@section('title', 'Devoluciones')

@section('js')
    @include('components..use.notification_success_error')
    @if ($errors->any())
    <script>
        $(document).ready(function() {
            Swal.fire({
                icon: 'info',
                title: 'Validación de campos',
                html: `
                    <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                `
            });
        });
    </script>
    @endif

@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Devoluciones {{$status == 0 ? 'Inhabilitadas':''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <div class="form-group">
                {{--<a class="btn btn-primary" href="{{route('devoluciones.create')}}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Nueva devolución"><i class="fa fa-plus"> Matriz</i></a>--}}

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

                                {{-- @if(!$item->sale_id)
                                    <a href="{{route('devoluciones.show', $item->id)}}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                @else
                                    <a href="{{route('devoluciones.showDevSale', [$item->id, $item->sale_id])}}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                @endif
                                
                                @if($status == 1)
                                    <a href="{{route('devoluciones.destroy', [$item->id, 0])}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                @elseif($status == 0)
                                    <a href="{{route('devoluciones.destroy', [$item->id, 1])}}" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i></a>
                                @endif --}}
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