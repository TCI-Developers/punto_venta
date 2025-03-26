@extends('adminlte::page')

@section('title', 'Proveedores')

@section('js')
    @include('components..use.notification_success_error')

    @if(isset($proveedores) && count($proveedores))
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
            <h2>Proveedores {{$status == 0 ? 'Inhabilitados':''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <div class="form-group">
                <a href="{{route('proveedor.create')}}" class="btn btn-success"><img src="{{asset('icons/plus.svg')}}" width="23" alt="icono plus"> &nbsp; Nuevo</a>
                
                <a href="{{route('proveedor.index', $status == 1 ? 0:1)}}" class="btn {{$status == 1 ? 'btn-light':'btn-primary'}} float-right"><img src="{{asset('icons/archive.svg')}}" width="23" alt="icono archive"> &nbsp; {{$status == 1 ? 'Inhabilitados':'Habilitados'}}</a>
            </div>

            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>RFC</th>
                        <th>Telefono</th>
                        <th>Saldo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $item)
                    <tr>
                        <td class="text-center">{{$item->code_proveedor}}</td>
                        <td class="">{{$item->name}}</td>
                        <td class="text-center">{{$item->rfc}}</td>
                        <td class="text-center">{{$item->phone}}</td>
                        <td class="text-center">$ {{number_format($item->saldo, 2)}}</td>
                        <td class="text-center">
                            <a href="{{route('proveedor.show', $item->id)}}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                            @if($status == 1)
                            <a href="{{route('proveedor.enable', [$item->id, $status])}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                            @else
                            <a href="{{route('proveedor.enable', [$item->id, $status])}}" class="btn btn-primary btn-sm"><img src="{{asset('icons/update.svg')}}" alt="icono update"></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="table-warning text-center">Sin turnos</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
  </div>
@stop