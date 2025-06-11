<div class="modal" id="modal_conf" tabindex="-1" aria-labelledby="modal_confLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_onfeLabel"><span id="title">Asignar</span> Turno y Roles</h5>
      </div>
      <form action="{{route('import.setConfDBLocal')}}" method="post">
      @csrf
        <div class="modal-body">
            <label for="password" class="col-12">Contraseña
                <input type="password" class="form-control" name="password">
            </label>                    
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" onClick="showModal('hide')"><i class="fa fa-times"></i> &nbsp; Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Asignar</button>
        </div>
      </form>
    </div>
  </div>
</div>