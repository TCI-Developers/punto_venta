<!-- Modal -->
<div class="modal" id="modal_create" tabindex="-1" aria-labelledby="modal_createLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_createLabel"><span id="title">Crear</span> rol</h5>
      </div>
      <form action="{{route('roles.store')}}" method="post" id="formRoles" edit="{{route('roles.update')}}" store="{{route('roles.store')}}">
      @csrf
        <input type="hidden" name="id">
        <div class="modal-body">
                <div class="row">
                    <label for="name" class="col-lg-12 col-md-12 col-sm-12">Nombre* <br>
                        <input type="text" class="form-control inputModal" name="name" id="name" placeholder="Nombre" required>
                    </label>
                    <label for="description" class="col-lg-12 col-md-12 col-sm-12">Descripción* <br>
                        <textarea class="form-control inputModal" name="description" id="description" cols="5" placeholder="Descripción"></textarea>
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