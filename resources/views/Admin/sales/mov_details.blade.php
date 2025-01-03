<div class="col-lg-12 col-md-12 col-sm-12">
    <div class="row col-lg-12 col-md-12 col-sm-12">
        <label for="presentation_id" class="col-lg-6 col-md-6 col-sm-12">Presentación* <br>
            <input type="text" class="form-control" name="presentation_id" id="presentation_id" placeholder="Presentación"
            wire:change="scaner_codigo" wire:model="scan_presentation_id" autofocus>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <span class="text-danger error" value="presentation_id" style="display:none;">Campo requerido</span>
            </div>
        </label>
        <label for="presentation_id" class="col-lg-6 col-md-6 col-sm-12"><br>
            <input type="text" class="form-control" readonly>
        </label>
    </div>

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
                <th>Descuento</th>
                <th>Total</th>
                @if($sale->amount_received == 0) 
                <th>Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody id="tbody_details">
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
                <td>$ {{ number_format($item->descuento, 2) }}</td>
                <td>$ {{number_format(($item->amount - $item->descuento), 2)}}</td>
                @if($sale->amount_received == 0) 
                <td>
                    <button type="button" class="btn btn-warning btn-sm" onClick="btnCantProduct({{$item->id}},{{$item->cant}})"><i class="fa fa-edit"></i></button>
                    <button type="button" class="btn btn-danger btn-sm" onClick="btnDestroyProduct({{$item->id}})"><i class="fa fa-trash"></i></button>
                </td>
                @endif
            </tr>
            @empty
            <tr id="trEmpty"><td colspan="10" class="table-warning text-center">Sin movimientos.</td></tr>
            @endforelse
        </tbody>
        <tbody id="tbody_total">
            <tr class="table-info"><td colspan="6"></td>
                <td class="text-right text-bold">Totales</td>
                <td class="text-center text-bold" >$ <span id="total_desc" class="badge badge-success">{{number_format($total_desc, 2)}}</span></td>
                <td class="text-center text-bold" >$ <span id="total_sale">{{number_format($total_sale, 2)}}</span></td>
                @if($sale->amount_received == 0)
                <td></td>
                @endif
            </tr>
        </tbody>
    </table>
    </div>
</div>