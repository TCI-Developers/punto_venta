<!-- Modal -->
<div class="modal" id="modal_create" tabindex="-1" aria-labelledby="modal_createLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_createLabel"><span id="title">Crear</span> turno</h5>
      </div>
      <form action="{{route('turnos.store')}}" method="post" id="formTurnos" edit="{{route('turnos.update')}}" store="{{route('turnos.store')}}">
      @csrf
        <input type="hidden" name="id">
        <div class="modal-body">
                <div class="row">
                    <label for="turno" class="col-lg-12 col-md-12 col-sm-12">Nombre* <br>
                        <input type="text" class="form-control inputModal" name="turno" id="turno" placeholder="Nombre" required>
                    </label>
                    <label for="description" class="col-lg-12 col-md-12 col-sm-12">Descripción* <br>
                        <textarea class="form-control inputModal" name="description" id="description" cols="5" placeholder="Descripción"></textarea>
                    </label>
                    <label for="entrada" class="col-lg-6 col-md-6 col-sm-6">Entrada* <br>
                        <input type="time" class="form-control inputModal" name="entrada" id="entrada" required>
                    </label>
                    <label for="salida" class="col-lg-6 col-md-6 col-sm-6">Salida* <br>
                        <input type="time" class="form-control inputModal" name="salida" id="salida" required>
                    </label>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onClick="btnCancel()">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnAddEdit">Agregar</button>
        </div>
      </form>
    </div>
  </div>
</div>