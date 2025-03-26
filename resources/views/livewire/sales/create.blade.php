<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2> <a href="{{route('sale.index')}}" class="btn btn-light float-left btn-sm"
            data-toggle="tooltip" data-placement="top" title="Regresar"><img src="{{asset('icons/back.svg')}}" alt="icon back" class="icon_img"></a>
                Crear Venta</h2>
        </div>
        <div class="card-body">
            <form action="{{route('sale.store')}}" method="post">
            @csrf
            <div class="row" wire:ignore>
                <!-- Cliente y fecha -->
                <label for="customer_id" class="col-lg-3 col-md-3 col-sm-12">Cliente* <br>
                    <select name="customer_id" id="customer_id" class="form-control selectpicker show-tick" data-live-search="true" 
                            data-size="8" title="Selecciona un cliente" required>
                        @forelse($customers as $item)
                        <option value="{{$item->id}}" {{$item->id == 1 ? 'selected':''}}>{{$item->name}}</option>
                        @empty
                        @endforelse
                    </select>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <span class="text-danger error" value="customer_id" style="display:none;">Campo requerido</span>
                    </div>
                </label>
                <label for="date" class="col-lg-3 col-md-3 col-sm-12" wire:ignore>Fecha* <br>
                    <input type="date" class="form-control " name="date" id="date" value="{{date('Y-m-d')}}" readonly required>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <span class="text-danger error" value="date" style="display:none;">Campo requerido</span>
                    </div>
                </label>
                <!-- Tipo de pago, metodo de pago y moneda -->
                <label for="payment_method_id" class="col-lg-2 col-md-2 col-sm-12">Metodo de Pago* <br>
                    <select name="payment_method_id" id="payment_method_id" class="form-control selectpicker show-tick " data-live-search="true" 
                            data-size="8" title="Metodo de pago" required>
                        @forelse($payment_methods as $item)
                        <option value="{{$item->id}}" {{$item->pay_method == 'PUE' ? 'selected':''}}>{{$item->pay_method}}</option>
                        @empty
                        @endforelse
                    </select>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <span class="text-danger error" value="payment_method_id" style="display:none;">Campo requerido</span>
                    </div>
                </label>
                <label for="type_payment" class="col-lg-2 col-md-2 col-sm-12">Tipo de Pago* <br>
                    <select name="type_payment" id="type_payment" class="form-control selectpicker show-tick" 
                            data-size="8" title="Metodo de pago">
                            <option value="efectivo" selected>Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                    </select>
                </label>
                <label for="coin" class="col-lg-2 col-md-2 col-sm-12">Moneda* <br>
                    <select name="coin" id="coin" class="form-control selectpicker show-tick " data-live-search="true" required>
                            <option value="MXN" selected>MXN</option>
                            <option value="USD" disabled>USD</option>
                    </select>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <span class="text-danger error" value="coin" style="display:none;">Campo requerido</span>
                    </div>
                </label>
            </div> <br>
            <button type="submit" class="btn btn-success float-right text-dark"><img src="{{asset('icons/plus.svg')}}" alt="icon plus" width="23"> &nbsp; Crear</button>
            </form>
        </div>
        @include('Admin.sales._modal')
  </div>