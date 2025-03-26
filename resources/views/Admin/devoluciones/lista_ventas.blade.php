@extends('adminlte::page')

@section('title', 'Listado de ventas')

@section('css')
    <style>
        .displayNone{
            display:none;
        }
    </style>
@stop

@section('js')
    @include('components.use.notification_success_error')

    @if(isset($sales) && count($sales))
        <script>
            $(document).ready(function(){
                $('table').dataTable();
            })
        </script>
    @endif
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h2>Listado de ventas</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="table">
                <thead>
                    <tr class="text-center table-info">
                        <th colspan="5">VENTAS</th>
                    </tr>
                    <tr class="text-center">
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total Venta</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $item)
                        <tr class="text-center">
                            <td>{{$item->folio}}</td>
                            <td>{{$item->customer->name}}</td>
                            <td>{{date('d-m-Y', strtotime($item->date))}}</td>
                            <td>$ {{number_format($item->total_sale)}}</td>
                            <td><a href="{{route('devoluciones.createSaleToDevolucion', $item->id)}}" class="btn btn-warning"><i class="fas fa-undo"></i></a></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="table-warnign">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop