<!-- Modal -->
<div class="modal" id="modal_create" tabindex="-1" aria-labelledby="modal_createLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_createLabel"><span id="title">Crear</span> Permiso</h5>
      </div>
      <form action="{{route('permission.store')}}" method="post" id="formRoles" edit="{{route('permission.update')}}" store="{{route('permission.store')}}">
      @csrf
        <input type="hidden" name="id">
        <div class="modal-body">
                <div class="row">
                    <label for="name" class="col-lg-12 col-md-12 col-sm-12">Modulo* <br>
                      <select name="module" id="module" class="form-control inputModal" required>
                        <option value=""></option>
                        @foreach($modules ?? [] as $item)
                          <option value="{{$item->name}}">{{$item->name}}</option>
                        @endforeach
                      </select>
                    </label>
                    <label for="submodule" class="col-12">Submodulo
                      <input type="text" class="form-control" name="submodule" value="punto_venta" readonly required>
                    </label>
                    <label for="action" class="col-12">Acción
                      <select name="action" id="action" class="form-control inputModal" required>
                        <option value="create">create</option>
                        <option value="update">update</option>
                        <option value="destroy">destroy</option>
                        <option value="auth">auth</option>
                        <option value="show">show</option>
                      </select>
                    </label>
                    </label>
                    <label for="description" class="col-lg-12 col-md-12 col-sm-12">Descripción* <br>
                        <textarea class="form-control inputModal" name="description" id="description" cols="5" placeholder="Descripción"></textarea>
                    </label>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onClick="btnCancel()">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnAddEdit">Crear</button>
        </div>
      </form>
    </div>
  </div>
</div>