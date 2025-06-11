<!-- Modal -->
<div class="modal modalProducts" id="modal_products" tabindex="-1" aria-labelledby="modal_productsLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore>
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal_productsLabel"><span id="titleSale">Agregar</span> Movimiento Almacen</h5>
      </div>
      <div class="modal-body col-12">
        <div class="mb-3">
          <input type="text" id="searchInput" class="form-control" placeholder="Buscar producto">
        </div>

          <div class="table-responsive">
              <table class="table table-striped table-bordered">
                  <thead>
                      <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Presentacion</th>
                        <th>Despiezado</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($products ?? [] as $index => $item)
                        <tr>
                          <td>{{$item->code_product}}</td>
                          <td>{{$item->description}}</td>
                          <td class="text-center">
                            @if(is_object($item->getPartToProduct)) 
                              <button type="button" class="btn btn-primary" wire:click="scaner_codigo('{{$item->getPartToProduct->code_bar}}')">Unidad: {{$item->getPartToProduct->getUnidadSat->name}} $ {{$item->getPartToProduct->price}}</button> 
                            @else NULL @endif</td>
                          <td class="text-center">
                            @if(is_object($item->getPartToProductDespiezado))
                              <button type="button" class="btn btn-success" wire:click="scaner_codigo('{{$item->getPartToProductDespiezado->code_bar}}')">$ {{$item->getPartToProductDespiezado->price}}</button>
                            @else NULL @endif
                          </td>
                        </tr>
                      @endforeach
                  </tbody>
              </table>
          </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onClick="okProduct()"><i class="fa fa-check"></i> Aceptar</button>
        <button type="button" class="btn btn-secondary" onClick="modalProductos('false')"><i class="fa fa-times"></i> Cancelar</button>
      </div>
    </div>
  </div>
</div>