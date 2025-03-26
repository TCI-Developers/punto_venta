<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>{{isset($compra) ? 'Actualizar compra':'Nueva compra'}}</h2>
        </div>

        <form action="{{route('compra.store', isset($compra) ? $compra->id:null )}}" method="post">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <a href="{{route('compra.index')}}" class="btn btn-success"><i class="fa fa-arrow-left"></i></a>
                    @if(isset($compra))
                    <label for="folio" class="col-lg-2 col-sm-12 float-right">
                        <input type="text" class="form-control text-center" name="folio" id="folio" placeholder="Folio" value="{{isset($compra) ? $compra->folio:''}}" readonly>
                    </label>
                    @endif
                    <hr>
                </div>

                <div class="row form-group">
                    <label for="proveedor_id" class="col-lg-4 col-sm-12" wire:ignore>Proveedor*
                        <select class="form-control selectpicker" name="proveedor_id" id="proveedor_id" data-live-search="true" onchange="diasCredito()" required>
                            <option value=""></option>
                            @forelse($proveedores as $item)
                                <option value="{{$item->id}}" days_credit="{{$item->days_credit}}" {{isset($compra) && $compra->proveedor_id == $item->id ? 'selected':'' }}>{{$item->name}}</option>
                            @empty
                            @endif
                        </select>
                    </label>
                    <label for="programacion_entrega" class="col-lg-4 col-sm-12">Programación de entrega
                        <input type="date" class="form-control" name="programacion_entrega" id="programacion_entrega" value="{{isset($compra) ? $compra->programacion_entrega:date('Y-m-d')}}">
                    </label>
                    @if(isset($compra))
                    <label for="fecha_recibido" class="col-lg-4 col-sm-12 float-right">Fecha Recibido
                        <input type="date" class="form-control" name="fecha_recibido" id="fecha_recibido" placeholder="" value="{{isset($compra) ? $compra->fecha_recibido:''}}" disabled>
                    </label>
                    @endif

                    <label for="plazo" class="col-lg-2 col-sm-12">Plazo
                        <input type="number" class="form-control" name="plazo" id="plazo" placeholder="0" value="{{isset($compra) ? $compra->plazo:''}}">
                    </label>

                    <label for="moneda" class="col-lg-2 col-sm-12" wire:ignore>Moneda
                        <select class="form-control selectpicker" name="moneda" id="moneda" data-live-search="true" required>
                            <option value="MXN" {{isset($compra) && $compra->moneda == 'MXN' ? 'selected':'selected' }}>Peso</option>
                            <option value="USD" {{isset($compra) && $compra->moneda == 'USD' ? 'selected':'' }}>Dolar</option>
                        </select>
                    </label>
                    <label for="tipo" class="col-lg-4 col-sm-12" wire:ignore>Tipo Compra
                        <select class="form-control selectpicker" name="tipo" id="tipo" data-live-search="true" required>
                            <option value="OC" {{isset($compra) && $compra->tipo == 'OC' ? 'selected':'selected' }}>Orden de compra</option>
                            <option value="S" {{isset($compra) && $compra->tipo == 'S' ? 'selected':'' }}>Servicio</option>
                        </select>
                    </label>
                    <label for="fecha_vencimiento" class="col-lg-4 col-sm-12">Vencimiento
                        <input type="date" class="form-control" name="fecha_vencimiento" id="fecha_vencimiento" value="{{isset($compra) ? $compra->fecha_vencimiento:''}}" readonly>
                    </label>

                    <label for="observaciones" class="col-12">Observaciones
                        <textarea class="form-control" name="observaciones" id="observaciones">{{isset($compra) ? $compra->observaciones:''}}</textarea>
                    </label>


                    @if(isset($compra))
                    <label for="subtotal" class="col-lg-4 col-sm-12">Subtotal Productos
                        <input type="number" class="form-control" name="subtotal" id="subtotal" placeholder="0.00" value="{{isset($compra) ? $compra->subtotal:''}}" disabled>
                    </label>
                    <label for="impuesto_productos" class="col-lg-4 col-sm-12">Impuestos Productos
                        <input type="number" class="form-control" name="impuesto_productos" id="impuesto_productos" placeholder="0.00" value="{{isset($proveedor) ? $proveedor->impuesto_productos:''}}" disabled>
                    </label>
                    @endif
                </div>
            </div>
            <div class="card-footer text-right">
                @if(isset($compra))
                <button type="button" class="btn btn-primary"><i class="fa fa-check"></i> {{isset($compra) ? 'Actualizar Requisición':'Guardar'}}</button>
                @else
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{isset($compra) ? 'Actualizar':'Guardar'}}</button>
                @endif
            </div>

            <div class="card-body">
                <label for="product_id" class="col-12" wire:ignore>Productos
                    <select name="product_id" id="product_id" class="form-control selectpicker" data-live-search="true" 
                    data-size="10" data-selected-text-format="count > 0" multiple wire:change="selectProducts" wire:model="select_product">
                        @foreach($products as $item)
                            <option value="{{$item->id}}">{{$item->code_product}} - {{$item->description}}</option>
                        @endforeach
                    </select>
                </label>
                <hr>
                <div class="form-goup table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="text-center">
                                <th>Codigo</th>
                                <th>Descripción</th>
                                <th>Entrada</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                                <th>Impuesto</th>
                                <th>Valor Impuesto</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="body_table">
                            @forelse($product_arr as $item)
                                <tr class="item_product">
                                    <td>{{$item->code_product}}</td>
                                    <td>{{$item->description}}</td>
                                    <td><input type="number" class="form-control text-right" name="entrada[]" placeholder="0" wire:change="entradaProduct({{$item->id}}, $event.target.value)" min="1"></td>
                                    <td class="text-right">$ {{number_format($item->precio, 2)}}</td>
                                    <td class="text-right">$ {{number_format($subtotal[$item->id] ?? $item->precio, 2)}}</td>
                                    <td class="text-center">{{$item->taxes}}</td>
                                    <td class="text-right">$ {{number_format($valor_impuesto[$item->id] ?? 0, 2)}}</td>
                                    <td class="text-right">$ {{number_format($total[$item->id] ?? $item->precio , 2)}}</td>
                                </tr>
                            @empty
                            <tr>
                                <td class="text-center table-warning" colspan="8">Sin productos seleccionados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <hr>

            </div>
        </form>
</div>