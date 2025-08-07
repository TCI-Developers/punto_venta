<!-- Modal -->
<div class="modal modalProducts" id="modal_products" tabindex="-1" aria-labelledby="modal_productsLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore.self>
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_productsLabel"><span id="titleSale">Agregar</span> Movimiento Almacen</h5>
      </div>
      <div class="modal-body col-12">
        <div class="mb-3">
          <input type="text" id="searchInput" class="form-control" placeholder="Buscar producto" wire:model.live="search">
        </div>

          <div class="table-responsive">
              <table class="table table-striped table-bordered">
                  <thead>
                      <tr>
                        <th>Cod Barras</th>
                        <th>Cod Prod</th>
                        <th>Descripción</th>
                        <th>Presentacion</th>
                      </tr>
                  </thead>
                  <tbody id="body_products">
                      @foreach($products ?? [] as $index => $item)
                        <tr>
                          <td>{{$item->code_bar}}</td>
                          <td>{{$item->getProduct->code_product}}</td>
                          <td>{{$item->getProduct->description}}</td>
                          <td class="text-center">
                            <button type="button" class="btn {{$item->cantidad_despiezado > 0 ? 'btn-success':'btn-primary'}} select-button" 
                             wire:click="scaner_codigo('{{$item->code_bar}}')">Unidad: {{$item->getUnidadSat->name}} $ {{$item->price}}</button> 
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
          </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onClick="modalProductos('false')"><i class="fa fa-times"></i> Cancelar</button>
      </div>
    </div>
  </div>
</div>