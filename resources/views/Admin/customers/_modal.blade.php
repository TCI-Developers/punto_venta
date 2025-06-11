<!-- Modal -->
<div class="modal" id="modal_customer" tabindex="-1" aria-labelledby="modal_customerLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_customerLabel"><span class="title">Agregar</span> Cliente</h5>
      </div>
      <form action="{{route('customer.store')}}" method="post" id="formCustomer">
      @csrf
        <input type="hidden" name="id" disabled>
        <div class="modal-body">
                <div class="row">
                    <label for="name" class="col-lg-12 col-md-12 col-sm-12">Nombre* <br>
                        <input type="text" class="form-control inputModal" name="name" id="name" placeholder="Nombre" required>
                    </label>
                    <label for="razon_social" class="col-lg-12 col-md-12 col-sm-12">Razón Social <br>
                        <input type="text" class="form-control inputModal" name="razon_social" id="razon_social" placeholder="Razón Social">
                    </label>
                    <label for="rfc" class="col-lg-6 col-md-6 col-sm-12">RFC <br>
                        <input type="text" class="form-control inputModal" name="rfc" id="rfc" placeholder="RFC">
                    </label>
                    <label for="postal_code" class="col-lg-6 col-md-6 col-sm-12">Codigo Postal <br>
                        <input type="text" class="form-control inputModal" name="postal_code" id="postal_code" placeholder="Codigo Postal">
                    </label>
                    <label for="regimen_fiscal" class="col-lg-12 col-md-12 col-sm-12">Regimen Fiscal <br>
                        <input type="text" class="form-control inputModal" name="regimen_fiscal" id="regimen_fiscal" placeholder="Regimen Fiscal">
                    </label>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="col-3 btn btn-secondary text_color" onClick="btnCancel()"><i class="fa fa-times"></i> Cancelar</button>
            <button type="submit" class="col-3 btn btn-primary text_color" id="btnAddEdit"><i class="fa fa-check"></i> <span class="title">Agregar</span></button>
        </div>
      </form>
    </div>
  </div>
</div>