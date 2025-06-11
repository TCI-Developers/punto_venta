@extends('adminlte::page')

@section('title', 'Devolución venta')

@section('css')
    <style>
        .displayNone{
            display:none;
        }
    </style>
@stop

@section('js')
    @include('components.use.notification_success_error')

    <script>
        //funcion para abrir modal
        function showModal(detail_cant_id, part_to_product_id, iva, ieps){
            let cant = $('#tr-'+detail_cant_id+' .cant').html(); 
            
            $('#detail_cant_id').val(detail_cant_id);               
            
            if(!$('#input-'+detail_cant_id+'-part_to_product_id').length){
                $('#formStore').append(`
                    <input type="hidden" id="input-${detail_cant_id}-part_to_product_id" name="part_to_product_id[]" value="${part_to_product_id}">
                    <input type="hidden" id="input-${detail_cant_id}-iva" name="iva[]" value="${iva}">
                    <input type="hidden" id="input-${detail_cant_id}-ieps" name="ieps[]" value="${ieps}">
                `);
            }else{
                $('#input-'+detail_cant_id+'-part_to_product_id').val(part_to_product_id);
                $('#input-'+detail_cant_id+'-iva').val(iva);
                $('#input-'+detail_cant_id+'-ieps').val(ieps);
            }

            $('#cant').attr('max', parseFloat(cant));
            $('#modal_cant').fadeIn();
        }

        //funcion para cerrar modal
        function btnCancelModal(){
            $('#cant').removeAttr('max').val('');
            $('#modal_cant').fadeOut();
        }

        //funcion para cerrar modal
        function devolucionCant(){
            if($('#td_dev').length){
                $('.tbody_dev').empty();
            }

            let detail_cant_id = $('#detail_cant_id').val(); //id detalle venta
            let data = [];

            data['detail_cant_id'] = detail_cant_id;
            data['cant'] = parseFloat($('#cant').val()); //cantidad a devolver
                data['cantidad_sale'] = parseFloat($('#tr-'+detail_cant_id+' .cant').html()); //total cantidad detalle venta
                data['code_product'] = $('#tr-'+detail_cant_id+' .code_product').html(); //codigo producto
                data['tipo_impuesto'] = $('#tr-'+detail_cant_id+' .tipo_impuesto').html(); //tipo impuesto
                data['unit_price'] = $('#tr-'+detail_cant_id+' .unit_price').html();  // precio unitario
                    data['unit_price'] = parseFloat(data['unit_price'].replace('$','')); //precio unitario parseado
                data['amount_impuesto'] = $('#tr-'+detail_cant_id+' .tipo_impuesto').attr('val'); //monto impuesto de producto
                data['subtotal'] = parseFloat(data['cant']) * data['unit_price']; //subtotal
                data['total_impuestos'] = data['subtotal'] * parseFloat(data['amount_impuesto']); //total impuestos
                data['descuento'] = $('#tr-'+detail_cant_id+' .descuento').attr('val'); //descuento presentacion
                    data['descuento'] = parseFloat(data['descuento'].replace('$','')); //descuento parseado
                data['total_descuento'] = data['cant'] * data['descuento']; //total descuento

            data['total_devolucion'] = (data['subtotal'] - data['total_descuento']) + data['total_impuestos']; //total devolucion

            if(data['cant'] <= data['cantidad_sale'] && !$('#tr_dev-'+detail_cant_id).length){//no puedes ingresar una cantidad mayo a la venta
                $('.tbody_dev').append(`
                    <tr class="text-center" id="tr_dev-${detail_cant_id}">
                        ${ tdTable(data) }
                    </tr>
                `);
                $('.displayNone').fadeIn();
                setData(data);
                btnCancelModal();
            }else if(data['cant'] <= data['cantidad_sale'] && $('#tr_dev-'+detail_cant_id).length){
                $('#tr_dev-'+detail_cant_id).empty().append(`
                    ${ tdTable(data) }
                `);
                $('.displayNone').fadeIn();
                setData(data);
                btnCancelModal();
            }else{
                alert();
            }
        }

        //funcion para hacer submit a form
        function buttonSubmit(){
            $('#formStore').submit();
        }

        //mostramos alerta
        function alert(){
            swal.fire('La cantidad ingresada es mayor a la de la venta.', '', 'info');
        }

        //funcion creamos los td que se ingresaran
        function tdTable(data){
            return `
                    <td>${ data['code_product'] }</td>
                    <td>${ data['cant'] }</td>
                    <td>${ data['tipo_impuesto'] }</td>
                    <td>$${ formatNumber(data['unit_price']) }</td>
                    <td>$${ formatNumber(data['total_impuestos']) }</td>
                    <td>$${ formatNumber(data['subtotal']) }</td>
                    <td>$${ formatNumber(data['total_descuento']) }</td>
                    <td>$${ formatNumber(data['total_devolucion']) }</td>
            `;
        }

        //funcion para llenar todos los inputs con la data
        function setData(data){
            let iva = $('#input-'+data['detail_cant_id']+'-iva').val();
            let ieps = $('#input-'+data['detail_cant_id']+'-ieps').val();
            let total = parseFloat(data['subtotal']) + parseFloat(iva) + parseFloat(ieps);
            
            if(!$('#input-'+data['detail_cant_id']+'-subtotal').length){
            $('#formStore').append(`
                <input type="hidden" id="input-${data['detail_cant_id']}-subtotal" name="subtotal[]" value="${data['subtotal']}">
                <input type="hidden" id="input-${data['detail_cant_id']}-unit_price" name="unit_price[]" value="${data['unit_price']}">
                <input type="hidden" id="input-${data['detail_cant_id']}-total" name="total[]" value="${total}">
                <input type="hidden" id="input-${data['detail_cant_id']}-cant" name="cant[]" value="${data['cant']}">
                <input type="hidden" id="input-${data['detail_cant_id']}-descuento" name="descuento[]" value="${data['descuento']}">
                <input type="hidden" id="input-${data['detail_cant_id']}-total-descuento" name="total_descuento[]" value="${data['total_descuento']}">
            `);
            }else{
                $('#input-'+data['detail_cant_id']+'-subtotal').val(data['subtotal']);
                $('#input-'+data['detail_cant_id']+'-unit_price').val(data['unit_price']);
                $('#input-'+data['detail_cant_id']+'-total').val(total);
                $('#input-'+data['detail_cant_id']+'-cant').val(data['cant']);
                $('#input-'+data['detail_cant_id']+'-descuento').val(data['descuento']);
                $('#input-'+data['detail_cant_id']+'-total-descuento').val(data['total_descuento']);
            }
        }

        //funcion para formatear el numero
        function formatNumber(num) {
            return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
        }

        //funcion para habilitar la edicion (solo admin)
        function editDevolucion(button){
            if($(button).hasClass('btn-warning')){ //edit
                $(button).removeClass('btn-warning').addClass('btn-danger').empty().append('<i class="fa fa-times"></i> Cancelar');
                $('.showEdit').attr('readonly', false).fadeIn();
                $('.showEditread').attr('readonly', false);
            }else{ //cancelar edit
                $(button).removeClass('btn-danger').addClass('btn-warning').empty().append('<i class="fa fa-edit"></i> Editar');
                $('.showEdit').attr('readonly', true).fadeOut();
                $('.showEditread').attr('readonly', true);
            }
        }
    </script>
@stop

@section('content')
        <div class="card card-primary">
            <div class="card card-header">
                <h2>Devolución Venta {{$sale->folio}}
                    <button type="button" class="btn btn-warning btn-sm float-right" onclick="editDevolucion(this)"><i class="fa fa-edit"></i> Editar</button>
                </h2>
            </div>
            <div class="card card-body text-center">
                <div class="row">
                    <label for="" class="col-4">Cliente: {{$sale->getClient->name}}</label>
                    <label for="" class="col-4">Vendedor: {{$sale->getUser->name}}</label>
                    <label for="" class="col-4">Total Venta: {{$sale->total_sale}}</label>
                </div>
            </div>
            <div class="card card-body">
                <table class="table table-striped table-bordered datatable">
                    <thead>
                        <tr class="text-center">
                            <th>Producto</th>
                            <th>Salida</th>
                            <th>Tipo Impuesto</th>
                            <th>Precio Unitario</th>
                            <th>Importe Impuesto</th>
                            <th>Subtotal</th>
                            <th>Descuento</th>
                            <th>Total</th>
                            <th class="{{isset($devolution) ? 'showEdit displayNone':''}}">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php 
                        $total_sale_ = 0;
                        $total_desc_ = 0;
                        $impuestos = 0;
                    @endphp
                        @forelse($sale_details as $item)
                            @foreach($item->getCantSalesDetail as $value)
                            @php
                                $total_sale_ += (($item->unit_price * $value->cant) - ($value->descuento*$value->cant)) + $impuestos;
                                $total_desc_ += ($value->descuento*$value->cant);
                                $impuestos += $item->iva != 0 ? $item->iva:$item->ieps;
                            @endphp

                            <tr class="text-center" id="tr-{{$value->id}}">
                                <td class="code_product">{{$item->getPartToProduct->getProduct->code_product}}</td>
                                <td class="cant">{{$value->cant}}</td>
                                <td class="tipo_impuesto" val="{{$item->getPartToProduct->getProduct->amount_taxes}}">
                                    @if($item->iva == 0 && $item->ieps == 0)
                                        SYS
                                    @else
                                        {{$item->iva != 0 ? 'IVA':'IEPS'}}
                                    @endif
                                </td>
                                <td class="unit_price">${{number_format($item->unit_price,2)}}</td>
                                <td class="total_impuestos">${{$item->iva != 0 ? number_format($item->iva,2):number_format($item->ieps,2)}}</td>
                                <td class="subtotal">${{number_format(($item->unit_price * $value->cant),2)}}</td>
                                <td class="descuento" val="{{$value->descuento}}">$ {{number_format($value->total_descuento, 2) }}</td>
                                <td class="total_sale">$ {{number_format(((($item->unit_price * $value->cant) - $value->total_descuento) + $impuestos), 2)}}</td>
                                <td class="{{isset($devolution) ? 'showEdit displayNone':''}}">
                                    @if(!isset($devolution))
                                    <button type="button" class="btn btn-warning btn-sm" onClick="showModal({{$value->id}}, {{$item->getPartToProduct->id}}, {{$item->iva}}, {{$item->ieps}})"><i class="fa fa-edit"></i></button>
                                    @elseif(!$devolution->hasCodeProduct($sale_details_dev ?? [], $item->getPartToProduct->getProduct->code_product))
                                    <button type="button" class="btn btn-warning btn-sm" onClick="showModal({{$value->id}}, {{$item->getPartToProduct->id}}, {{$item->iva}}, {{$item->ieps}})"><i class="fa fa-edit"></i></button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="9" class="table-warning">Sin movimientos.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                <br>
                <table class="table table-striped table-bordered table-secondary">
                    <thead>
                        <tr>
                            <th colspan="9">Productos Devolución</th>
                        </tr>
                        <tr class="text-center">
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Tipo Impuesto</th>
                            <th>Precio Unitario</th>
                            <th>Importe Impuesto</th>
                            <th>Subtotal</th>
                            <th>Descuento</th>
                            <th>Total</th>
                            <th class="{{isset($devolution) ? 'showEdit displayNone':''}}">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="tbody_dev">
                        @if(isset($devolution))
                            @php 
                                $total_sale_ = 0;
                                $total_desc_ = 0;
                                $impuestos = 0;
                            @endphp
                            @forelse($sale_details_dev as $item)
                                @foreach($item->getCantSalesDetailDev as $value)
                                @php
                                    $impuestos += $item->iva != 0 ? $item->iva:$item->ieps;
                                    $total_sale_ += (($item->unit_price * $value->cant) - ($value->descuento*$value->cant)) + $impuestos;
                                    $total_desc_ += ($value->descuento*$value->cant);
                                @endphp
                                    <tr class="text-center" id="tr_dev-{{$value->id}}">
                                        <td class="code_product">{{$item->getPartToProduct->getProduct->code_product}}</td>
                                        <td class="cant">{{$value->cant}}</td>
                                        <td class="tipo_impuesto" val="{{$item->getPartToProduct->getProduct->amount_taxes}}">
                                            @if($item->iva == 0 && $item->ieps == 0)
                                                SYS
                                            @else
                                                {{$item->iva != 0 ? 'IVA':'IEPS'}}
                                            @endif
                                        </td>
                                        <td class="unit_price">${{number_format($item->unit_price,2)}}</td>
                                        <td class="total_impuestos">${{$item->iva != 0 ? number_format($item->iva,2):number_format($item->ieps,2)}}</td>
                                        <td class="subtotal">${{number_format(($item->unit_price * $value->cant),2)}}</td>
                                        <td class="descuento" val="{{$value->descuento}}">$ {{number_format($value->total_descuento, 2) }}</td>
                                        <td class="total_sale">$ {{number_format(((($item->unit_price * $value->cant) - $value->total_descuento) + $impuestos), 2)}} </td>
                                        <td class="text-center {{isset($devolution) ? 'showEdit displayNone':''}}">
                                            <a href="{{route('devoluciones.deleteDetailDev', [$devolution->id, $item->id])}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                <tr><td class="table-warning text-center" colspan="8" id="td_dev">Sin productos</td></tr>
                                @endif
                                @endforeach
                        @else
                        <tr><td class="table-warning text-center" colspan="9" id="td_dev">Sin productos</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="row card-footer">
                <div class="col-8">
                    <form action="{{route('devoluciones.store', $devolution->id ?? null)}}" method="post" id="formStore">
                        @csrf
                        <input type="hidden" name="sale_id" value="{{$sale->id}}">
                        <label for="" class="col-12">Fecha
                            <input type="date" class="form-control w-50 showEditread" name="fecha_devolucion" id="fecha_devolucion" value="{{$devolution->fecha_devolucion ?? date('Y-m-d')}}" readonly>
                        </label>
                        <label for="notes" class="col-12">Nota
                            <textarea class="form-control showEditread" name="notes" id="notes" required {{isset($devolution) ? 'readonly':''}}>{{$devolution->description ?? ''}}</textarea>
                        </label>
                    </form>
                </div>
                <div class="col-4" style="display:flex; ustify-content: center; align-items: center; justify-content: space-around;">
                    <button class="btn btn-success displayNone" onClick="buttonSubmit()"><i class="fa fa-check"></i> Aceptar</button>
                    <a href="{{route('devoluciones.index')}}" class="btn btn-secondary"><i class="fa fa-times"></i> Cancelar</a>
                </div>
            </div>
        </div>

        <!-- Modal -->
    <div class="modal modalSale" id="modal_cant" tabindex="-1" aria-labelledby="modal_cantLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modal_cantLabel">Cantidad Devolución</h5>
        </div>
        <div class="modal-body col-12">
            <label for="cant" id="label_cant_prod" class="col-12 text-center">Cantidad
                <input type="number" class="form-control text-center" id="cant" min="1">
                <input type="hidden" id="detail_cant_id">
            </label>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onClick="devolucionCant()"><i class="fa fa-check"></i> Aceptar</button>
            <button type="button" class="btn btn-secondary" onClick="btnCancelModal()"><i class="fa fa-times"></i> Cancelar</button>
        </div>

        </div>
    </div>
    </div>
@stop