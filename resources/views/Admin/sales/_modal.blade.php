<!-- Modal -->
<div class="modal modalSale" id="modal_cant" tabindex="-1" aria-labelledby="modal_cantLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_cantLabel"><span id="titleSale">Agregar</span> Movimiento Almacen</h5>
      </div>
      <div class="modal-body col-12">
          <label for="new_cant_prod" id="label_cant_prod" class="col-12 text-center">Cantidad
              <input type="number" class="form-control text-center" id="update_cant_prod" min="1">
              <input type="hidden" class="form-control text-center" id="presentation_id" >
          </label>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onClick="updateCant()"><i class="fa fa-check"></i> Aceptar</button>
        <button type="button" class="btn btn-secondary" onClick="btnCancelModal()"><i class="fa fa-times"></i> Cancelar</button>
      </div>

    </div>
  </div>
</div>