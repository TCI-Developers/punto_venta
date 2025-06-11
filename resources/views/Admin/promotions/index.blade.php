@extends('adminlte::page')

@section('title', 'Promociones')

@section('js')
    @include('components..use.notification_success_error')
@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Promociones {{$status == 0 ? 'Inhabilitadas':''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <div class="form-group">
                <a class="btn btn-primary" href="{{route('promos.create')}}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Nueva promoción"><i class="fa fa-plus"></i></a>
                
                <a href="{{route('promos.index', $status == 0 ? 1:0)}}" class="btn {{$status == 0 ? 'btn-success':'btn-secondary'}} float-right" data-bs-toggle="tooltip" data-bs-placement="top" 
                    title="Usuarios {{$status == 0 ? 'Habilitados':'Inhabilitados'}}"><i class="fa fa-folder"></i></a>
            </div>

            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Sucursal</th>
                        <th>Promoción</th>
                        <th>Cantidad</th>
                        <th>Vigencia</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $index => $item)
                       <tr>
                            <td>{{$item->getBranch->name}}</td>
                            <td>{{$item->description}}</td>
                            <td class="text-center">{{$item->cantidad_producto}}X{{$item->cantidad_productos_a_pagar}}</td>
                            @if($item->vigencia_cantidad > 0 && date('d/m/Y', strtotime($item->vigencia_fecha)) != '')
                            <td class="text-center">{{$item->vigencia_cantidad}}/{{date('d/m/Y', strtotime($item->vigencia_fecha))}}</td>
                            @elseif($item->vigencia_cantidad > 0)
                            <td class="text-center">{{$item->vigencia_cantidad}}</td>
                            @else
                            <td class="text-center">{{date('d/m/Y', strtotime($item->vigencia_fecha))}}</td>
                            @endif
                            <td class="text-center">
                                <a href="{{route('promos.show', $item->id)}}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                @if($status == 1)
                                <a href="{{route('promos.destroy', [$item->id, 0])}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                @elseif($status == 0)
                                <a href="{{route('promos.destroy', [$item->id, 1])}}" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i></a>
                                @endif

                            </td>
                       </tr>
                    @empty
                    <tr><td colspan="5" class="table-warning text-center">Sin promociones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
  </div>

@stop