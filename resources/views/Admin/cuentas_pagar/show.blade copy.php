@extends('adminlte::page')

@section('title', 'Cuentas por pagar')

@section('css')
    <style>
        .displayNone{
            display:none;
        }
    </style>
@stop

@section('js')
    @include('components..use.notification_success_error')

    <script>
        //funcion para mostrar los valores en campos
        function edit(id, date, importe){
            $('#btnSubmit').html('Actualizar');
            $('#buttonCancel').fadeIn();
            $('#divBtns').fadeIn();

            $('input[name=cxp_detail_id]').val(id);
            $('input[name=date]').val(date);
            $('input[name=importe]').val(importe);
        }

        //funcion para cancelar la actualizacion
        function btnCancel(date){
            let status = '{{$status}}';
            if(status){
                $('#divBtns').fadeOut();
            }

            $('#btnSubmit').html('Guardar');
            $('#buttonCancel').fadeOut();

            $('input[name=cxp_detail_id]').val('');
            $('input[name=date]').val(date);
            $('input[name=importe]').val('');
        }
    </script>
@stop 

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Cuentas por pagar</h2>
        </div>

        <div class="card-body table-responsive">
            <div class="row form-group">
                <a href="{{route('cxp.index')}}" class="btn btn-success"><i class="fa fa-arrow-left"></i></a>
            </div>
            <hr>
            
            <form action="{{route('cxp.store', $cuenta->id)}}" method="post">
            @csrf
            <input type="hidden" name="cxp_detail_id">

            <div class="row col-12 text-center badge-secondary">
                <h4 class="col-lg-3 col-sm-12"><strong>Fecha Vencimiento:</strong> <br>{{date('d-m-y', strtotime($cuenta->fecha_vencimiento)) }}</h4>
                <h4 class="col-lg-3 col-sm-12"><strong>Subtotal:</strong> <br> $ {{number_format($cuenta->subtotal, 2)}}</h4>
                <h4 class="col-lg-3 col-sm-12"><strong>Impuestos:</strong> <br> $ {{number_format($cuenta->impuestos, 2)}}</h4>
                <h4 class="col-lg-3 col-sm-12"><strong>Total:</strong> <br> $ {{number_format($cuenta->total, 2)}}</h4>
            </div>

            <div class="row col-12">
                <label for="date" class="col-6">Fecha
                    <input type="date" class="form-control" name="date" value="{{date('Y-m-d')}}">
                </label>
                <label for="importe" class="col-6">Importe
                    <input type="number" class="form-control" name="importe" placeholder="0.00" step="0.01">
                </label>

                <div class="col-12 text-right {{$status ? 'displayNone':''}} " id="divBtns">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <span id="btnSubmit">Guardar</span></button>
                    <button type="button" class="btn btn-secondary displayNone" id="buttonCancel" onClick="btnCancel('{{date('Y-m-d')}}')"><i class="fa fa-times"></i> Cancelar </button>
                </div>
            </div>
            </form>

            <hr>

            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr>
                        <th colspan="4" class="text-center text-bold text-lg">Resgistro de pagos</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-right text-bold text-lg"><strong>Se debe:</strong> $ {{number_format($total_debe, 2)}}</th>
                    </tr>
                    <tr class="text-center">
                        <th>Fecha</th>
                        <th>Importe</th>
                        <th>Restante</th>
                        @if(Auth::User()->hasAnyRole(['admin', 'root']))
                            <th>Acción</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $debe = 0;
                    @endphp
                    @forelse($cuenta->getDetails ?? [] as $item)
                    @php 
                        $debe += $item->importe;
                    @endphp
                    <tr>
                        <td class="text-center">{{date('d-m-Y', strtotime($item->date))}}</td>
                        <td class="text-right">$ {{number_format($item->importe, 2)}}</td>
                        <td class="text-right">$ {{number_format(($cuenta->total - $debe),2)}}</td>
                        @if(Auth::User()->hasAnyRole(['admin', 'root']))
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm" onClick="edit({{$item->id}}, '{{$item->date}}', {{$item->importe}})"><i class="fa fa-edit"></i></button>
                            <a href="{{route('cxp.destroy', $item->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="4" class="table-warning text-center">Sin registros</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
  </div>
@stop