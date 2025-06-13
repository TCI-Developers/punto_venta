<div class="card card-primary" wire:ignore>
        <div class="form-group card-header with-border text-center">
            <h2><a href="{{route('sale.index')}}" class="btn btn-success float-left btn-sm"
            data-toggle="tooltip" data-placement="top" title="Regresar"><i class="fa fa-arrow-left"></i></a>
                Venta</h2>
        </div>
        <div class="card-body">
            <form action="{{route('sale.update', $sale->id)}}" method="post" id="formSale">
            @csrf
            <input type="hidden" name="status" value="">
            <div class="row">
                <!-- Cliente y fecha -->
                <label for="customer_id" class="col-lg-4 col-md-4 col-sm-12">Cliente*<br>
                    <select name="customer_id" id="customer_id" class="form-control selectpicker show-tick input_sale" data-live-search="true" 
                            data-size="8" title="Selecciona un cliente" disabled required>
                        @forelse($customers as $item)
                        <option value="{{$item->id}}" {{$sale->customer_id == $item->id ? 'selected':''}}>{{$item->name}}</option>
                        @empty
                        @endforelse
                    </select>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <span class="text-danger error d-none" value="customer_id" >Campo requerido</span>
                    </div>
                </label>

                <!-- Tipo de pago, metodo de pago y moneda -->
                <label for="payment_method_id" class="col-lg-2 col-md-2 col-sm-12">Metodo de Pago* <br>
                    <select name="payment_method_id" id="payment_method_id" class="form-control selectpicker show-tick input_sale" data-live-search="true" 
                            data-size="8" title="Metodo de pago" onchange="metodoPago()" disabled required>
                        @forelse($payment_methods as $item)s
                        <option value="{{$item->id}}" data-name="{{$item->pay_method}}" {{$sale->payment_method_id == $item->id ? 'selected':''}}>{{$item->pay_method}}</option>
                        @empty
                        @endforelse
                    </select>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <span class="text-danger error d-none" value="payment_method_id" >Campo requerido</span>
                    </div>
                </label>
                <label for="type_payment" class="col-lg-2 col-md-2 col-sm-12">Tipo de Pago* <br>
                    <select name="type_payment" id="type_payment" class="form-control selectpicker input_sale show-tick" 
                            data-size="8" title="Metodo de pago" disabled>
                            <option value="efectivo" {{$sale->type_payment == 'efectivo' ? 'selected':''}} disabled>Efectivo</option>
                            <option value="tarjeta" {{$sale->type_payment == 'tarjeta' ? 'selected':''}} disabled>Tarjeta</option>
                    </select>
                </label>
                <label for="coin" class="col-lg-2 col-md-2 col-sm-12">Moneda* <br>
                    <select name="coin" id="coin" class="form-control selectpicker show-tick input_sale" data-live-search="true" disabled required>
                            <option value="MXN" {{$sale->coin == 'MXN' ? 'selected':''}}>MXN</option>
                            <option value="USD" {{$sale->coin == 'USD' ? 'selected':''}} disabled>USD</option>
                    </select>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <span class="text-danger error d-none" value="coin" >Campo requerido</span>
                    </div>
                </label>

                <label for="date" class="col-lg-2 col-md-2 col-sm-12" wire:ignore>Fecha* <br>
                    <input type="date" class="form-control" name="date" id="date" value="{{$sale->date}}" disabled required>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <span class="text-danger error d-none" value="date" >Campo requerido</span>
                    </div>
                </label>
                <!-- Amounts -->
                <div class="col-lg-12 col-md-12 col-sm-12 text-center {{(float)$sale->amount_received > 0 ? '':'d-none'}}" id="div_amounts">
                    <hr>
                    <label for="amount_received" class="col-lg-3 col-md-3 col-sm-12 padding-0"> Monto recibido<br>
                        <div class="input-group mb-3" style="width:100%;">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" name="amount_received" id="amount_received" class="form-control input_amounts input_sale text-center" step="0.01" 
                            placeholder="0" value="{{(float)$sale->amount_received == 0 ? '':$sale->amount_received}}" onchange="getChange(this.value)" readonly>
                        </div>
                    </label>
                    <label for="total_sale" class="col-lg-3 col-md-3 col-sm-12 padding-0"> Total venta<br>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" name="total_sale" id="total_sale" class="form-control input_amounts input_sale text-center" placeholder="0" 
                            value="{{round((float)$sale->amount_received > 0 ? $sale->total_sale:$sale->getAmount($sale->id), 2)}}" step="0.01" readonly>
                        </div>
                    </label>
                    <label for="change" class="col-lg-3 col-md-3 col-sm-12 padding-0"> Cambio <br>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" name="change" id="change" class="form-control input_amounts input_sale text-center" step="0.01" placeholder="0" step="0.01" value="{{$sale->change}}" readonly>
                        </div>
                    </label>
                </div>
            </div> <br>
                <!-- Si no se a cobrado, podemos agregar mas movimientos -->
                @if((float)$sale->amount_received == 0) 
                {{-- <button type="button" class="btn btn-info float-left" onClick="btnOpenModal()" id="btnAddMov">Agregar Movimiento Almacen</button>--}}
                <button type="button" class="btn btn-primary float-right" onClick="editSale()" id="btnEnableEdit">Habilitar Edición</button> 
                <button type="button" class="btn btn-primary float-right" onClick="showTicket()">Ticket</button> 

                <button type="button" class="btn btn-success float-right mr-2 d-none" onclick="submitSale()" id="btnAcept">Aceptar</button> 
                {{--<button type="submit" class="btn btn-success float-right mr-2 d-none" id="btnAcept">Aceptar</button> --}}
                <button type="submit" class="btn btn-success float-right mr-5 d-none" id="btnUpdateSale">Actualizar venta</button> 
                <button type="button" class="btn btn-light float-right mr-5 d-none" id="btnCancelSale" onClick="cancelEditSale()">Cancelar</button> 
                @endif

                @if($sale->status <= 1)
                <!-- Si existen movimientos y no se a cobrado -->
                <button type="button" class="btn btn-warning float-right mr-2 
                    {{count($sale->getDetails) && (float)$sale->amount_received === 0 || !count($sale->getDetails) && (float)$sale->amount_received === 0 ? '':'d-none'}}"
                    onClick="cobrar()" id="btnCobro">Cobrar</button> 
                @endif
            </form>
        </div>
        @include('Admin.sales.mov_details')
        
        @if(isset($devoluciones) && count($devoluciones))
            @include('Admin.sales.devoluciones')
        @endif

        @include('Admin.sales._modal')
        @include('Admin.sales._modal_products')
        @include('Admin.sales._modal_ticket')
  </div>