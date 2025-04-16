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
                        <th>Tipo</th>
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
                        </td>
                        <td class="text-center">
                            <a href="{{route('compra.show', $item->id)}}" class="btn btn-warning btn-sm"><i class="fa fa-eye"></i></a>
                            <a href="{{route('compra.pdf', $item->id)}}" class="btn btn-info btn-sm"><i class="fa fa-file"></i></a>
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