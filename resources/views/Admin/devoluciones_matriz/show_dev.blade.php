<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devoluciones</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js']) 
    @include('components.use.link_scripts_glabal')
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error') 

   <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Devolucion Compra</h2>
        </div>

            <div class="card-body">
                <div class="form-group align-items-center">
                    <a href="{{route('devoluciones.index')}}" class="col-lg-1 col-sm-12 btn btn-success"><i class="fa fa-arrow-left"></i></a>
                    @if(isset($compra))
                    <label for="folio" class="col-lg-2 col-sm-12 float-right">
                        <input type="text" class="form-control text-center" name="folio" id="folio" placeholder="Folio" value="{{isset($compra) ? $compra->folio:''}}" readonly>
                    </label>
                    @endif
                    <hr>
                </div>

                <div class="row form-group">
                    <label for="proveedor_id" class="col-lg-4 col-sm-12" wire:ignore>Proveedor
                        <select class="form-control" name="proveedor_id" id="proveedor_id" data-live-search="true"
                            onchange="diasCredito(this)" required disabled>
                            @if(is_null($compra->proveedor_id))
                                <option value="">MATRIZ</option>
                            @else 
                            <option value=""></option>
                            @forelse($proveedores ?? [] as $item)
                                <option value="{{$item->id}}" {{$compra->proveedor_id == $item->id ? 'selected':''}} 
                                    days_credit="{{$item->credit_days}}" {{isset($compra) && $compra->proveedor_id == $item->id ? 'selected':'' }}>{{$item->name}} - {{$item->credit_days}}</option>
                            @empty
                            @endforelse
                            @endif
                        </select>
                    </label>

                    <label for="plazo" class="col-lg-2 col-sm-12">Plazo
                        <input type="number" class="form-control" name="plazo" id="plazo" placeholder="0" value="{{isset($compra) ? $compra->plazo:''}}" readonly>
                    </label>

                    <label for="moneda" class="col-lg-2 col-sm-12" wire:ignore>Moneda
                        <select class="form-control selectpicker" name="moneda" id="moneda" data-live-search="true" required disabled>
                            <option value="MXN" {{isset($compra) && $compra->moneda == 'MXN' ? 'selected':'selected' }}>Peso</option>
                            <option value="USD" {{isset($compra) && $compra->moneda == 'USD' ? 'selected':'' }}>Dolar</option>
                        </select>
                    </label>
                    <label for="tipo" class="col-lg-4 col-sm-12" wire:ignore>Tipo Compra
                        <select class="form-control selectpicker" name="tipo" id="tipo" data-live-search="true" required disabled>
                            <option value="OC" {{isset($compra) && $compra->tipo == 'OC' ? 'selected':'selected' }}>Orden de compra</option>
                            <option value="S" {{isset($compra) && $compra->tipo == 'S' ? 'selected':'' }}>Servicio</option>
                        </select>
                    </label>

                    @if(isset($compra) && $compra->status != 1)
                    <label for="programacion_entrega" class="col-lg-4 col-sm-12">Programación de entrega
                        <input type="date" class="form-control" name="programacion_entrega" id="programacion_entrega" value="{{isset($compra) ? $compra->programacion_entrega:date('Y-m-d')}}" disabled>
                    </label>
                    @endif

                    @if(isset($compra))
                    @if($compra->status == 4 || $compra->status == 5)
                    <label for="fecha_recibido" class="col-lg-4 col-sm-12 float-right">Fecha Recibido
                        <input type="date" class="form-control" name="fecha_recibido" id="fecha_recibido" placeholder="" value="{{isset($compra) ? $compra->fecha_recibido:''}}" disabled>
                    </label>
                    @endif
                    @endif

                    @if(isset($compra))
                    @if($compra->status == 4 || $compra->status == 5)
                    <label for="fecha_vencimiento" class="col-lg-4 col-sm-12">Vencimiento
                        <input type="date" class="form-control" name="fecha_vencimiento" id="fecha_vencimiento" value="{{isset($compra) ? $compra->fecha_vencimiento:''}}" disabled>
                    </label>
                    @endif
                    @endif

                    <label for="observaciones" class="col-12">Observaciones
                        <textarea class="form-control" name="observaciones" id="observaciones" readonly>{{isset($compra) ? $compra->observaciones:''}}</textarea>
                    </label>


                    @if(isset($compra))
                    <label for="subtotal" class="col-lg-4 col-sm-12">Subtotal Productos
                        <input type="text" class="form-control" name="subtotal" id="subtotal" placeholder="0.00" value="$ {{isset($compra) ? number_format($compra->subtotal, 2):''}}" disabled>
                    </label>
                    <label for="impuesto_productos" class="col-lg-4 col-sm-12">Impuestos Productos
                        <input type="text" class="form-control" name="impuesto_productos" id="impuesto_productos" placeholder="0.00" value="$ {{isset($compra) ? number_format($compra->impuesto_productos, 2):''}}" disabled>
                    </label>
                    <label for="total" class="col-lg-4 col-sm-12">Total
                        <input type="text" class="form-control" name="total" id="total" placeholder="0.00" value="$ {{isset($compra) ? number_format($compra->total, 2):''}}" disabled>
                    </label>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="form-goup table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="text-center">
                                <th>Codigo</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Impuesto</th>
                                <th>Total Impuesto</th>
                                <th>Subtotal</th>
                                <th>Total</th>
                                @if(Auth::User()->hasAnyRole(['root', 'admin']) && isset($compra) && $compra->status == 1)<th>Acción</th>@endif
                            </tr>
                        </thead>
                        <tbody id="body_table">
                            @if(isset($devolution))
                                    <tr class="item_product">
                                        <td class="text-center">{{$devolution->getProduct->code_product}}</td>
                                        <td>{{$devolution->getProduct->description}}</td>
                                        <td class="text-center">{{$devolution->cantidad}}</td>
                                        <td class="text-right">$ {{number_format(($devolution->subtotal / $devolution->cantidad), 2)}}</td>
                                        <td class="text-right">$ {{number_format($devolution->impuesto, 2)}}</td>
                                        <td class="text-right">$ {{number_format($devolution->total_impuesto, 2)}}</td>
                                        <td class="text-right">$ {{number_format($devolution->subtotal, 2)}}</td>
                                        <td class="text-right">$ {{number_format($devolution->total, 2)}}</td>
                                    </tr>
                            @else
                                <tr wire:ignore>
                                    <td class="text-center table-warning" colspan="9">Sin productos.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
</div>
</div>

</main>   
</body>
</html>