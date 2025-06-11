<!-- Modal -->
<div class="modal" id="modal_presentations" tabindex="-1" aria-labelledby="modal_presentationsLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_presentationsLabel"><span id="title">Agregar</span> Presentación</h5>
      </div>
      <form action="{{route('product.storePresentationProduct')}}" method="post" id="formCategory" edit="{{route('product.updatePresentationProduct')}}" store="{{route('product.storePresentationProduct')}}">
      @csrf
        <input type="hidden" name="id">
        <div class="modal-body">
                <div class="row">
                    <label for="type" class="col-lg-12 col-md-12 col-sm-12">Presentación* <br>
                        <input type="text" class="form-control inputModal" name="type" id="type" placeholder="Tipo" required>
                    </label>
                    <label for="unidad_sat_id" class="col-lg-12 col-md-12 col-sm-12">Unidades SAT* <br>
                        <select name="unidad_sat_id" id="unidad_sat_id" class="form-control selectpicker show-tick" data-live-search="true" title="Selecciona una unidad" required>
                            @forelse($unidades_sat as $item)
                              <option value="{{$item->id}}">{{$item->clave_unidad}} - {{$item->name}}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                    <label for="description" class="col-lg-12 col-md-12 col-sm-12">Descripción <br>
                        <textarea class="form-control inputModal" name="description" id="description" cols="5" placeholder="Descripción"></textarea>
                    </label>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light text-dark" onClick="cancelModal()"><img src="{{asset('icons/cancel.svg')}}" alt="icon cancel" width="23">&nbsp;Cancelar</button>
            <button type="submit" class="btn btn-primary text-dark" id="btnAddEdit"><img src="{{asset('icons/save.svg')}}" alt="icon save" width="23">&nbsp;Agregar</button>
        </div>
      </form>
    </div>
  </div>
</div>