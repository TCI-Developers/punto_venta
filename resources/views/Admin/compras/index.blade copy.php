@extends('adminlte::page')

@section('title', 'Compras')

@section('css')

@stop

@section('js')
    @include('components..use.notification_success_error')

    @if(isset($compras) && count($compras))
        <script>
            $(document).ready(function(){
                $('.datatable').dataTable();
            })
        </script>
    @endif
@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Compras</h2>
        </div>

        <div class="card-body table-responsive">
            <div class="form-group">
                <a href="{{route('compra.create')}}" class="btn btn-success"><img src="{{asset('icons/plus.svg')}}" width="23" alt="icono plus"> &nbsp; Nueva</a>
                <a href="{{route('proveedor.index', $status == 1 ? 0:1)}}" class="btn {{$status == 1 ? 'btn-light':'btn-primary'}} float-right"><img src="{{asset('icons/archive.svg')}}" width="23" alt="icono archive"> &nbsp; {{$status == 1 ? 'Inhabilitados':'Habilitados'}}</a>
            </div>

            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Folio</th>
                        <th>Proveedor</th>
                        <th>Fecha Requisición</th>
                        <th>Observaciones</th>
                        <th>Importe General</th>
                        <th>Tipo/Status</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compras as $item)
                    <tr>
                        <td class="text-center">{{$item->folio}}</td>
                        <td>{{$item->getProveedor->name}}</td>
                        <td class="text-center">{{date('d-m-Y', strtotime($item->created_at))}}</td>
                        <td>{{$item->observaciones}}</td>
                        <td class="text-right">{{number_format($item->total, 2)}}</td>
                        <td class="text-center">
                            <span class="badge {{$item->tipo == 'OC' ? 'badge-success':'badge-info'}}">{{$item->tipo == 'OC' ? 'Orden de compra':'Servicio'}}</span> 
                            @if($item->getCuentaPagar)
                            <br>
                            @if($item->getCuentaPagar->status == 2)
                                <span class="badge badge-success">Pagada</span>
                            @else
                                <span class="badge {{$item->getCuentaPagar->status == 1 ? 'badge-warning':'badge-danger'}}">{{$item->getCuentaPagar->status == 1 ? 'Pendiente':'Cancelada'}}</span>
                            @endif
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Acción
                                </button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                <a class="dropdown-item" href="{{route('compra.show', $item->id)}}"><i class="fa fa-eye"></i> &nbsp; Visualizar</a>
                                <a class="dropdown-item" href="{{route('compra.pdf', $item->id)}}"><i class="fa fa-file"></i> &nbsp; PDF</a>
                                @if($item->getCuentaPagar)
                                    <a class="dropdown-item" href="{{route('cxp.show', $item->getCuentaPagar->id)}}"><i class="fa fa-address-book"></i> &nbsp; Cuenta por pagar</a>
                                @endif 
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="table-warning text-center">Sin compras</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
  </div>
@stop