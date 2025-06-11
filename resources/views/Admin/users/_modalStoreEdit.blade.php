<!-- Modal -->
<div class="modal" id="users" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="usersLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="usersLabel"><span class="title">Crear</span> Usuario</h3>
      </div>
        <form action="{{route('users.store')}}" edit="{{route('users.update')}}" store="{{route('users.store')}}" method="post" id="formUser">
        @csrf
        <input type="hidden" name="user_id" class="inputs">
            <div class="modal-body">
                    <div class="row">
                        <label for="name" class="col-lg-12 col-md-12 col-sm-12">Nombre
                            <input type="text" name="name" id="name" class="form-control inputs" placeholder="Nombre" required>
                        </label>
                        <label for="email" class="col-lg-12 col-md-12 col-sm-12">Email
                            <input type="text" name="email" id="email" class="form-control inputs" placeholder="Email" required>
                        </label>
                        <label for="phone" class="col-lg-12 col-md-12 col-sm-12">Telefono
                            <input type="text" name="phone" id="phone" class="form-control inputs" placeholder="Dirección" required>
                        </label>

                        <label for="" class="col-lg-12 col-md-12 col-sm-12 d-none" id="switch_pass">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="switchPass">
                                <label class="custom-control-label" for="switchPass">Actualizar Contraseña</label>
                            </div>
                        </label>

                        <label for="password" class="col-lg-12 col-md-12 col-sm-12">Contraseña
                            <input type="password" name="password" id="password" class="form-control pass inputs" placeholder="Contraseña">
                        </label>
                        <label for="confirmedPass" class="col-lg-12 col-md-12 col-sm-12">Confirmar Contraseña
                            <input type="password" name="confirmedPass" id="confirmedPass" class="form-control pass inputs" placeholder="Contraseña">
                        </label>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onClick="modal('null')"><i class="fa fa-times"></i> Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <span class="title">Crear</span></button>
            </div>
        </form>
    </div>
  </div>
</div>