<!-- Modal -->
<div class="modal" id="modal_create" tabindex="-1" aria-labelledby="modal_createLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_createLabel"><span id="title">Asignar</span> Turno y Roles</h5>
      </div>
      <form action="{{route('users.setRolesTurnos')}}" method="post" id="formCategory" edit="{{route('users.updateRolesTurnos')}}" store="{{route('users.setRolesTurnos')}}">
      @csrf
        <input type="hidden" name="id">
        <div class="modal-body">
                <div class="row">
                    <label for="role_id" class="col-lg-12 col-md-12 col-sm-12">Roles <br>
                        <select name="role_id[]" id="role_id" class="form-control selectpicker show-tick" data-live-search="true" title="Selecciona roles" multiple>
                          @forelse($roles as $item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                          @empty
                          @endforelse
                        </select>
                    </label>
                    <label for="turno_id" class="col-lg-12 col-md-12 col-sm-12">Turnos <br>
                        <select name="turno_id" id="turno_id" class="form-control selectpicker show-tick" data-live-search="true" title="Selecciona un turno">
                          @forelse($turnos as $item)
                            <option value="{{$item->id}}">{{$item->turno}} - {{date('g:i a', strtotime($item->entrada))}} | {{date('g:i a', strtotime($item->salida))}}</option>
                          @empty
                          @endforelse
                        </select>
                    </label>
                </div> 
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" onClick="btnCancel()"><img src="{{asset('icons/cancel.svg')}}" alt="icon cancel" width="23"> &nbsp; Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnAddEdit">Asignar</button>
        </div>
      </form>
    </div>
  </div>
</div>