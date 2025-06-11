<div class="card-body">
    @if(!isset($compra) || isset($compra) && $compra->status == 1)
        <label for="product_id" class="col-12" wire:ignore>Productos
            <select name="" id="product_id" class="form-control" data-live-search="true" show-tick data-style="btn-secondary"
            data-size="10" data-selected-text-format="count" wire:change="selectProducts" wire:model="select_product" multiple>
                @foreach($products as $index => $item)
                    <option value="{{$item->id}}" {{isset($compra) && $compra->hasProduct($item->id) ? 'disabled':''}}>{{$item->code_product}} - {{$item->description}}</option>
                @endforeach
            </select>
        </label>
    @endif
    <hr>
    <div class="form-goup table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr class="text-center">
                    <th>Codigo</th>
                    <th>Descripción</th>
                    <th>Entrada</th>
                    @if(isset($compra) && $compra->status == 4 || isset($compra) && $compra->status == 5 ) <th>Recibido</th> @endif
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                    <th>Impuesto</th>
                    <th>Valor Impuesto</th>
                    <th>Total</th>
                    @if(Auth::User()->hasAnyRole(['root', 'admin']) && isset($compra) && $compra->status == 1)<th>Acción</th>@endif
                </tr>
            </thead>
            <tbody id="body_table">
                @if(isset($compra))
                    @forelse($product_saved as $item)
                        <tr class="item_product">
                            <td>{{$item->getProduct->code_product}}</td>
                            <td>{{$item->descripcion_producto}}</td>
                            <td wire:ignore><input type="number" class="form-control text-right entradas" name="entrada_saved[{{$item->id}}]" placeholder="0" 
                                        onchange="entradaProduct({{$item->id}}, this.value, '{{$item->getProduct->taxes}}', {{$item->getProduct->amount_taxes}})" min="1" 
                                        value="{{$item->getEntrada->entrada}}" {{isset($compra) ? 'disabled':''}} required></td>
                            @if($compra->status == 4 || $compra->status == 5)
                                <td><input type="number" class="form-control text-right" name="recibido[{{$item->getEntrada->id}}]" placeholder="0" 
                                onchange="recibidoProduct({{$item->id}}, this.value, '{{$item->getProduct->taxes}}', {{$item->getProduct->amount_taxes}})"
                                    min="1" value="{{$compra->status == 4 ? $item->getEntrada->entrada:$item->getEntrada->recibido}}" {{$compra->status == 5 ? 'readonly':''}}></td>
                            @endif
                            <td class="text-right precioUnitario-{{$item->id}}">$ {{number_format($item->precio_unitario, 2)}}</td>
                            <td class="text-right"><input type="text" class="form-control text-right" name="subtotal_saved[{{$item->id}}]" wire:model.defer="subtotal[{{$item->id}}]" value="$ {{number_format(isset($subtotal[$item->id]) ? $subtotal[$item->id]:$item->subtotal, 2)}}" readonly></td>
                            <td class="text-center">{{$item->taxes}}</td>
                            <td class="text-right"><input type="text" class="form-control text-right" name="impuestos_saved[{{$item->id}}]" wire:model.defer="valor_impuesto[{{$item->id}}]" value="$ {{number_format(isset($valor_impuesto[$item->id]) ? $valor_impuesto[$item->id]:$item->impuestos, 2)}}" readonly></td>
                            <td class="text-right"><input type="text" class="form-control text-right" name="total_saved[{{$item->id}}]" wire:model.defer="total[{{$item->id}}]" value="$ {{number_format(isset($total[$item->id]) ? $total[$item->id]:$item->total, 2)}}" readonly></td>
                            @if(Auth::User()->hasAnyRole(['root', 'admin']) && $compra->status == 1)
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-sm btnEdit" onclick="btnEditEntrada(this)"><i class="fa fa-edit"></i></button>
                                    <button type="button" class="btn btn-success btn-sm btnOkEntrada d-none" onclick="btnOk(this)"><i class="fa fa-check"></i></button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="btnDestroyEntrada({{$item->id}})"><i class="fa fa-trash"></i></button>
                                </td>
                            @endif
                        </tr>
                    @empty
                    <tr wire:ignore>
                        <td class="text-center table-warning" colspan="9">Sin productos seleccionados.</td>
                    </tr>
                    @endforelse
                @endif

                @forelse($product_arr as $item)
                    <tr class="item_product">
                        <input type="hidden" name="product_id[]" value="{{$item->id}}">
                        <td>{{$item->code_product}}</td>
                        <td>{{$item->description}}</td>
                        <td><input type="number" class="form-control text-right" name="entrada[]" placeholder="0" wire:change="entradaProduct({{$item->id}}, $event.target.value)" min="1" required></td>
                        <td class="text-right">$ {{number_format($item->precio, 2)}}</td>
                        <td class="text-right"><input type="text" class="form-control text-right" name="subtotal[]" value="$ {{number_format($subtotal[$item->id] ?? $item->precio, 2)}}" readonly></td>
                        <td class="text-center">{{$item->taxes}}</td>
                        <td class="text-right"><input type="text" class="form-control text-right" name="impuestos[]" value="$ {{number_format($valor_impuesto[$item->id] ?? 0, 2)}}" readonly></td>
                        <td class="text-right"><input type="text" class="form-control text-right" name="total[]" value="$ {{number_format($total[$item->id] ?? $item->precio , 2)}}" readonly></td>
                        @if(Auth::User()->hasAnyRole(['root', 'admin']) && isset($compra) && $compra->status == 1)<td></td>@endif
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
    <hr>

</div>