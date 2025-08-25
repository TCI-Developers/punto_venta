<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Productos {{count($lineas_id)}}</h2>
        </div>
        <div class="form-group card-header with-border">
            <a href="{{route('product.index')}}" class="btn btn-success btn-sm"><i class="fa fa-arrow-left"></i></a>
            <button class="btn btn-primary btn-sm float-right" wire:click="saveNewStock"><i class="fa fa-save"></i> Guardar</button>
        </div>
        <div class="card-body">
            <div class="row col-12" style="align-items: center">
                <label for="linea_id" class="col-lg-4 col-md-4 col-sm-12" wire:ignore>
                    <select name="linea_id" id="linea_id" class="form-control"
                            data-live-search="true" show-tick data-style="btn-secondary"
                            data-selected-text-format="count"
                            wire:model="linea_id"
                            wire:change="getProducts($event.target.value)"
                            multiple>
                        @foreach($lineas ?? [] as $item)
                            <option value="{{ $item->id }}"
                                @if(in_array($item->id, $lineas_id ?? [])) selected @endif>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label for="">
                    <button type="button" class="btn {{$status == 1 ? 'btn-warning':'btn-primary'}} btn-sm" 
                            wire:click="bloquearLinea" 
                            {{ count($lineas_id) ? '' : 'disabled'}}>
                        {{$status == 1 ? 'Bloquear':'Habilitar'}}
                    </button>
                </label>
            </div>

            <div class="table-responsive" style="height: calc(100vh - 250px); overflow-y: auto;">
                <table class="table table-striped table-bordered" wire:ignore>
                    <thead>
                        <tr class="text-center">
                            <th>Codigo Producto</th>
                            <th>Linea</th>
                            <th>Existencia</th>
                            <th class="showInputs {{ count($lineas_id) ? '' : 'd-none' }}">Nuevo Stock</th>
                            <th class="showInputs {{ count($lineas_id) ? '' : 'd-none' }}">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="bodyTable">
                        <tr><td colspan="5" class="table-warning">Sin productos.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
</div>