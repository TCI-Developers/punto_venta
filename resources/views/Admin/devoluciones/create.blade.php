@extends('adminlte::page')

@section('title', 'Crear devolución')

@section('css')
    <style>
        .displayNone{
            display:none;
        }
    </style>
@stop

@section('js')
    @include('components.use.notification_success_error')
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

    @if(isset($sales) && count($sales))
        <script>
            $(document).ready(function(){
                $('table').dataTable();
            })
        </script>
    @endif

    <script>
        $(document).ready(function(){
            //funcion para agregar la cantidad de producto al input product_presentation_id
            $('#product_id').on('change', function(){
                let part_to_product_id = $(this).find('option:selected').attr('part_to_product');
                $('input[name=part_to_product_id]').val(part_to_product_id);
                // $('input[name=product_presentation_id]').val(cant);
            });
        })

        //funcion validar la cantidad de productos de la venta y los que se devolveran
        function products(){
            let select = $('#product_id option:selected').attr('cant');
            let input = $('#cantidad').val();
            if(select != '' && input != '' ){
               if(select < input){
                    $('#cantidad').val('');
                    swal.fire('No puedes usar una cantidad mayor a la del producto seleccionado.', '', 'error');
                }
            }
        }
    </script>
@stop

@section('content')
    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>{{$devolucion ? 'Actualizar':'Nueva'}} Devolución {{isset($sales) && count($sales) ? 'de Ventas':''}}</h2>
        </div>
        @if($devolucion)
        <form action="{{route('devoluciones.update', $devolucion->id)}}" method="post">
        @else
        <form action="{{route('devoluciones.store')}}" method="post">
            @if(isset($sale))
            <input type="hidden" name="sale_id" value="{{$sale->id}}">
            <input type="hidden" name="part_to_product_id">
            <!-- <input type="hidden" name="product_presentation_id"> -->
            @endif
        @endif
            @csrf

            @if(isset($sales) && count($sales))
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
                                <td><a href="{{route('devoluciones.createSale', ['null', $item->id])}}" class="btn btn-warning"><i class="fas fa-undo"></i></a></td>
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
            @endif

            <div class="card-body {{isset($sales) && count($sales) ? 'displayNone':''}}">
                <div class="row form-group">
                    @if(isset($sale))
                    <div class="row col-12 card-footer">
                        <h3 class="col-12">Detalles de la venta</h3>
                        <label for="" class="col-lg-4 col-md-6 col-sm-12 text-center">FOLIO: <p>{{$sale->folio}}</p></label>
                        <label for="" class="col-lg-4 col-md-6 col-sm-12 text-center">FECHA VENTA: <p>{{$sale->date}}</p></label>
                        <label for="" class="col-lg-4 col-md-6 col-sm-12 text-center">CLIENTE: <p>{{$sale->customer->name}}</p></label>
                    </div>
                    @endif
                    <label for="product_id" class="col-lg-6 col-md-6 col-sm-6">Productos
                            @if(isset($productos))
                                <select name="product_id" id="product_id" class="form-control selectpicker" title="Producto" data-live-search="true">
                                    @foreach($productos as $item)
                                        <option value="{{$item->id}}" {{isset($devolucion) && $devolucion->product_id == $item->id ? 'selected':''}}>{{$item->code_product}} - {{$item->description}}</option>
                                    @endforeach
                                </select>
                            @else
                                <select name="product_id" id="product_id" class="form-control selectpicker" title="Producto" data-live-search="true" onchange="products()">
                                @foreach($productos_sale as $item)
                                    <option value="{{$item['id']}}" cant="{{$item['cantidad']}}" part_to_product="{{$item['part_product_id']}}"
                                    {{isset($devolucion) && $devolucion->product_id == $item['id'] ? 'selected':''}}>
                                        {{$item['product']}} ({{$item['cantidad']}} {{$item['product_presentation']}})
                                    </option>
                                @endforeach
                                </select>
                                
                            @endif
                            
                    </label>

                    <label for="description" class="col-lg-6 col-md-6 col-sm-6">Descripción
                            <input type="text" class="form-control" name="description" id="description" placeholder="Descripción" value="{{$devolucion->description ?? ''}}">
                    </label>
                    <label for="cantidad" class="col-lg-6 col-md-6 col-sm-6">Cantidad
                            <input type="number" class="form-control" name="cantidad" id="cantidad" placeholder="0" value="{{$devolucion->cantidad ?? ''}}" onchange="products()">
                    </label>
                    <label for="fecha_devolucion" class="col-lg-6 col-md-6 col-sm-6">Fecha devolución
                            <input type="date" class="form-control" name="fecha_devolucion" id="fecha_devolucion" value="{{$devolucion->fecha_devolucion ?? date('Y-m-d')}}">
                    </label>
                </div>
            </div>
            <div class="card-footer">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-check"></i> {{$devolucion ? 'Actualizar':'Crear'}}</button> 
                    <a href="{{route('devoluciones.index')}}" class="btn btn-danger float-right mr-2"><i class="fa fa-times"></i> Cancelar</a>
                </div>
                </div>
            </div>
        </form>
  </div>

@stop