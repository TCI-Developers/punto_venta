<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <script src="{{asset('js/devoluciones/create_devolucion.js')}}"></script>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')

        @if(session('ticket'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    $('#modalTicket').show();
                });
            </script>
        @endif


        <div class="card card-primary">
            <div class="card card-header text-center">
                <h2>
                    <a href="{{route('devoluciones.index')}}" class="btn btn-success btn-sm float-left"><i class="fa fa-arrow-left"></i></a>
                    Devolución Venta {{$sale->folio}}
                    @if(isset($devolution) && auth()->user()->hasPermissionThroughModule('devoluciones','punto_venta','update'))
                    <button type="button" class="btn btn-warning btn-sm float-right" onclick="editDevolucion(this)"><i class="fa fa-edit"></i> Editar</button>
                    @endif
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
                            <th class="{{isset($devolution) ? 'showEdit d-none':''}}">Acciones</th>
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
                                <td class="{{isset($devolution) ? 'showEdit d-none':''}}">
                                    @if(!isset($devolution))
                                    <button type="button" class="btn btn-warning btn-sm" onclick="showModal({{$value->id}}, {{$item->getPartToProduct->id}}, {{$item->iva}}, {{$item->ieps}})"><i class="fa fa-edit"></i></button>
                                    @elseif(!$devolution->hasCodeProduct($sale_details_dev ?? [], $item->getPartToProduct->getProduct->code_product))
                                    <button type="button" class="btn btn-warning btn-sm" onclick="showModal({{$value->id}}, {{$item->getPartToProduct->id}}, {{$item->iva}}, {{$item->ieps}})"><i class="fa fa-edit"></i></button>
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
                            <th class="{{isset($devolution) ? 'showEdit d-none':''}}">Acción</th>
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
                                        <td class="text-center {{isset($devolution) ? 'showEdit d-none':''}}">
                                            @if(auth()->user()->hasPermissionThroughModule('devoluciones','punto_venta','destroy'))
                                            <a href="{{route('devoluciones.deleteDetailDev', [$devolution->id, $item->id])}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                            @endif
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

            <div class="card-footer">
                <div class="row">
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
                        <button class="btn btn-success d-none" onclick="buttonSubmit()"><i class="fa fa-check"></i> Aceptar</button>
                        <a href="{{route('devoluciones.index')}}" class="btn btn-secondary"><i class="fa fa-times"></i> Cancelar</a>
                    </div>
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
</main>   
    @include('Admin.devoluciones._modal_ticket')
</body>
</html>