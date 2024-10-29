<!-- Modal -->
<div class="modal modalSale" id="modal_create" tabindex="-1" aria-labelledby="modal_createLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_createLabel"><span id="titleSale">Agregar</span> Movimiento Almacen</h5>
      </div>
      <form action="{{route('sale.storeDetail')}}" method="post" id="formSale" edit="{{route('sale.updateDetail')}}" store="{{route('sale.storeDetail')}}">
      @csrf
        <input type="hidden" name="sale_id" value="{{$sale->id ?? ''}}">
        <input type="hidden" name="mov_sale_id">
        <input type="hidden" name="taxes">
        <input type="hidden" name="total_taxes">
                <div class="row col-lg-12 col-md-12 col-sm-12">
                  <label for="product_id" class="col-lg-12 col-md-12 col-sm-12">Producto* <br>
                      <select name="product_id" id="product_id" class="form-control selectpicker show-tick select_modal" data-live-search="true" 
                              data-size="8" title="Selecciona un cliente" wire:change="getPrices($event.target.value)" required>
                          @forelse($products as $item)
                          <option value="{{$item->id}}">{{$item->code_product}} - {{$item->description}}</option>
                          @empty
                          @endforelse
                      </select>
                      <div class="col-lg-12 col-md-12 col-sm-12">
                          <span class="text-danger error" value="product_id" style="display:none;">Campo requerido</span>
                      </div>
                  </label>
                  <label for="presentation_id" class="col-lg-8 col-md-8 col-sm-12">Presentación* <br>
                      <select name="presentation_id" id="presentation_id" class="form-control selectpicker show-tick select_modal" data-live-search="true" 
                              data-size="8" title="Selecciona la presentación" onchange="setPrice(this)" disabled required>
                      </select>
                      <div class="col-lg-12 col-md-12 col-sm-12">
                          <span class="text-danger error" value="presentation_id" style="display:none;">Campo requerido</span>
                      </div>
                  </label>
                  <label for="cant" class="col-lg-4 col-md-4 col-sm-12">Salida* <br>
                    <input type="number" class="form-control " name="cant" id="cant" placeholder="0" onChange="getAmount()" required>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <span class="text-danger error" value="cant" style="display:none;">Campo requerido</span>
                    </div>
                </label>

                <label for="price" class="col-lg-4 col-md-4 col-sm-12">Precio <br>
                <input type="number" class="form-control" id="price" name="price" readonly>
                </label>
                <div class="row col-lg-8 col-md-8 col-sm-12">
                  <label for="taxes" class="col-lg-4 col-md-4 col-sm-4 text-center">Tipo Impuesto: <br> <span id="taxes"></span></label>
                  <label for="iva_ieps" class="col-lg-4 col-md-4 col-sm-4 text-center"><span id="title_taxes"></span> <br> <span id="iva_ieps"></span></label>
                  <label for="total_taxes" class="col-lg-4 col-md-4 col-sm-4 text-center">Impuestos <br> <span id="total_taxes"></span></label>
                </div>
               
                <label for="subtotal" class="col-lg-6 col-md-6 col-sm-12">Subtotal <br>
                    <input type="text" class="form-control " name="subtotal" id="subtotal" value="0" readonly>
                </label>
                <label for="amount" class="col-lg-6 col-md-6 col-sm-12">Total <br>
                    <input type="text" class="form-control " name="amount" id="amount" value="0" readonly>
                </label>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onClick="closeModal()">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnSubmit">Agregar</button>
        </div>
      </form>
    </div>
  </div>
</div>