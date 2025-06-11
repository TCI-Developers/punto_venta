<!-- Modal -->
<div class="modal" id="modal_presentation" tabindex="-1" aria-labelledby="modal_presentationLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
       
            <div class="col-lg-6 col-md-6 col-sm-12">
                <h5 class="modal-title" id="modal_presentationLabel"><span id="title">Asignar</span> Presentación a <span id="title_modal"></span></h5>
                <h5 class="modal-title displayNone" id="title_form_presentation"><span id="title">Crear</span> Presentación</h5>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12" id="divButtons">
                <button type="button" class="btn btn-info btn-sm float-right showPresentationsDelete" id="btnPresentations-0" status="0">
                    <img src="{{asset('icons/list.svg')}}" alt="icon list"> Presentaciones de producto Eliminadas</button>
                <button type="button" class="btn btn-primary btn-sm float-right showPresentationsDelete displayNone" id="btnPresentations-1" status="1">
                    <img src="{{asset('icons/list.svg')}}" alt="icon list">Presentaciones de producto</button>
            </div>
        
      </div>
      <form wire:submit="save_presentation_in_product" id="form_add_presentation_product">
        <input type="hidden" name="id" wire:model.defer="product_id">
        <input type="hidden" name="part_product_id" wire:model.defer="part_product_id">
        <div class="modal-body">
                <div class="form-group">
                    <div class="card card-body">
                    <div class="row" id="div_presentacion_precio">
                        <label for="presentation_type_id" class="col-lg-8 col-md-8 col-sm-12">Presentación* <br>
                            <select id="presentation_type_id" class="form-control selectpicker inputModal" title="Selecciona una presentación" wire:model.defer="modal_presentation_type_id">
                                <option value="new" style="background-color:#32c4fed9;">- Crear presentación</option>
                            @forelse($presentations as $item)
                                    <option value="{{$item->id}}" {{$modal_presentation_type_id == $item->id ? 'selected':''}}>{{$item->type}}</option>
                                @empty
                                @endforelse
                            </select>
                        </label>
                        <label for="price" class="col-lg-4 col-md-4 col-sm-12">Precio* <br>
                            <input type="number" name="price" id="price" class="form-control inputModal" placeholder="0" wire:model.defer="modal_price">
                        </label>
                        <label for="code_bar" class="col-lg-12 col-md-12 col-sm-12">Codigo <br>
                            <input type="text" name="code_bar" id="code_bar" class="form-control inputModal" placeholder="Codigo" wire:model.defer="modal_code_bar" value="{{$modal_code_bar}}">
                        </label>
                        <label for="promotion_id" class="col-lg-12 col-md-12 col-sm-12">promociones <br> {{-- Revisar aqui como se guardara si es muchas a 1 o muchas a muchas --}}
                            <select id="promotion_id" class="form-control selectpicker inputModal" title="Selecciona un descuento">
                                @forelse($promotions as $item)
                                    <option value="{{$item->id}}" {{$modal_promotion_id == $item->id ? 'selected':''}}>{{$item->description}}</option>
                                @empty
                                @endforelse
                            </select>
                        </label>
                    </div>
                    </div>
                    
                    <div class="card card-body">
                        <h5 class="text-center text-bold col-lg-12 col-md-12 col-sm-12">Descuento 
                            <button type="button" class="btn btn-info btn-sm float-right" onClick="showOrHideDescuentos('show')" id="btnShow">
                                <i class="fa fa-arrow-down"></i>
                            </button>
                            <button type="button" class="btn btn-info btn-sm float-right displayNone" onClick="showOrHideDescuentos('hide')" id="btnHide">
                                <i class="fa fa-arrow-up "></i>
                            </button>
                        </h5>
                        <div class="row" id="div_descuento" style="display:none;">
                            <label for="tipo_descuento" class="col-lg-6 col-md-6 col-sm-12" wire:ignore>Monto o Porcentaje
                                <select name="tipo_descuento" id="tipo_descuento" class="form-control selectpicker" title="Selecciona una opción" onchange="selectsDescuento(this.value)" wire:model.defer="modal_tipo_descuento">
                                    <option value="monto" selected>Monto</option>
                                    <option value="porcentaje">Porcentaje</option>
                                </select>
                            </label>
                            <label for="monto_porcentaje" class="col-lg-6 col-md-6 col-sm-12"><span id="title_monto_porcentaje">Monto</span>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">$</span>
                                    </div>
                                    <input type="number" class="form-control" name="monto_porcentaje" id="monto_porcentaje" placeholder="0" aria-describedby="basic-addon1" wire:model.defer="modal_monto_porcentaje">
                                </div>                                
                            </label>

                            <label for="vigencia_cantidad_fecha" class="col-lg-6 col-md-6 col-sm-12" wire:ignore>Vigencia por Cantidad o Fecha
                                <select name="vigencia_cantidad_fecha" id="vigencia_cantidad_fecha" class="form-control selectpicker" title="Selecciona una opción" onchange="selectsDescuento(this.value)" wire:model.defer="modal_vigencia_cantidad_fecha">
                                    <option value="fecha" selected>Fecha</option>
                                    <option value="cantidad">Cantidad</option>
                                </select>
                            </label>
                            <label for="vigencia" class="col-lg-6 col-md-6 col-sm-12"><span id="title_vigencia">Fecha</span>
                                <input type="date" class="form-control" name="vigencia" id="vigencia_fecha" value="{{date('Y-m-d')}}" wire:model.defer="modal_vigencia_fecha">
                                <input type="number" class="form-control displayNone" name="vigencia" id="vigencia_cantidad" placeholder="0" wire:model.defer="modal_vigencia_cantidad">
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="max-height:350px;">
                        <br>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Presentación</th>
                                    <th class="text-center">Precio</th>
                                    <th class="text-center">Descuento</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="body_table">
                                <tr><td colspan="4" class="table-warning text-center">Sin presentaciones.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onClick="cancelModal()"><img src="{{asset('icons/cancel.svg')}}" alt="icon cancel"> &nbsp; Cancelar</button>
            <button type="button" class="btn btn-light displayNone" id="btnCancelUpdate" onClick="cancelUpdate(1)"> <img src="{{asset('icons/cancel.svg')}}" alt="icon cancel"> &nbsp; Cancelar Actualización</button>
            <button type="submit" class="btn btn-primary" id="btnSubmitModal"><img src="{{asset('icons/save.svg')}}" alt="icon save"> &nbsp; <span id="titleButton">Asignar</span></button>
        </div>
    </form>
    @include('Admin.products.form_presentation')
    </div>
  </div>
</div>