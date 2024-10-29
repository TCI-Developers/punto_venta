<div class="col-lg-12 col-md-12 col-sm-12">
    <div class="table-responsive">
    <table class="table table-striped table-bordered datatable">
        <thead>
            <tr class="text-center">
                <th>Producto</th>
                <th>Salida</th>
                <th>Unidad</th>
                <th>Tipo Impuesto</th>
                <th>Precio Unitario</th>
                <th>Importe Impuesto</th>
                <th>Subtotal</th>
                <th>Total</th>
                @if($sale->amount_received == 0) 
                <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($sales_detail as $item)
            <tr class="text-center">
                <td>{{$item->getPartToProduct->getProduct->code_product}}</td>
                <td>{{$item->cant}}</td>
                <td>{{$item->getPartToProduct->getPresentation->getUnidadSat->clave_unidad}} - {{$item->getPartToProduct->getPresentation->getUnidadSat->name}}</td>
                <td>
                    @if($item->iva == 0 && $item->ieps == 0)
                        SYS
                    @else
                        {{$item->iva != 0 ? 'IVA':'IEPS'}}
                    @endif
                </td>
                <td>${{number_format($item->unit_price,2)}}</td>
                <td>${{$item->iva != 0 ? number_format($item->iva,2):number_format($item->ieps,2)}}</td>
                <td>${{number_format($item->subtotal,2)}}</td>
                <td>${{number_format($item->amount, 2)}}</td>
                @if($sale->amount_received == 0) 
                <td>
                    <button type="button" class="btn btn-warning btn-sm btnEditMov" wire:click="updateMovDetail({{$item->id}})"><i class="fa fa-edit"></i></button>
                </td>
                @endif
            </tr>
            @empty
            <tr id="trEmpty"><td colspan="9" class="table-warning text-center">Sin Ventas</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>