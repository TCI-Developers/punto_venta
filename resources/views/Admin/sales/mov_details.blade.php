<div class="col-lg-12 col-md-12 col-sm-12">
    <div class="row col-lg-12 col-md-12 col-sm-12">
        <label for="presentation_id" class="col-lg-5 col-md-5 col-sm-12">Presentación* <br>
            <input type="text" class="form-control" name="presentation_id" id="presentation_id" placeholder="Presentación"
            wire:keydown.enter="scaner_codigo" wire:model.defer="scan_presentation_id" autofocus {{$sale->status == 2 ? 'readonly':''}}>
            <!-- wire:change="scaner_codigo" wire:model="scan_presentation_id" autofocus {{$sale->status == 2 ? 'readonly':''}}> -->
            <div class="col-lg-12 col-md-12 col-sm-12">
                <span class="text-danger error" value="presentation_id" style="display:none;">Campo requerido</span>
            </div>
        </label>
        <label for="presentation_id" class="col-lg-5 col-md-5 col-sm-12"><br>
            <input type="text" class="form-control" readonly>
        </label>
        <label for="presentation_id" class="col-lg-2 col-md-2 col-sm-12 text-center"> <br>
                <button type="button" class="btn btn-info btn-sm" onclick="modalProductos()" {{$sale->status == 2 ? 'disabled':''}}>Producto Manual</button>
        </label>
    </div>

    <div class="table-responsive">
    <table class="table table-striped table-bordered">
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
            @php 
                $total_sale_ = 0;
                $total_desc_ = 0;
                $impuestos = 0;
            @endphp

            @forelse($sales_detail as $index => $item)
            @if(count($item->getCantSalesDetail))

            @foreach($item->getCantSalesDetail as $value)
            @php
                $impuestos += $item->iva != 0 ? $item->iva:$item->ieps;
                $total_sale_ += (($item->unit_price * $value->cant) - ($value->total_descuento)) + $impuestos;
                $total_desc_ += $value->total_descuento ?? 0;
            @endphp

            <tr class="text-center" ident="tr-{{$item->getPartToProduct->getProduct->code_product}}">
                <td>{{$item->getPartToProduct->getProduct->code_product}}</td>
                <td>{{$value->cant}}</td>
                <td>{{$item->getPartToProduct->getProduct->unit}} - {{$item->getPartToProduct->getProduct->unit_description}}</td>
                <td>
                    @if($item->iva == 0 && $item->ieps == 0)
                        SYS
                    @else
                        {{$item->iva != 0 ? 'IVA':'IEPS'}}
                    @endif
                </td>
                <td>$ {{number_format($item->unit_price,2)}}</td>
                <td>$ {{$item->iva != 0 ? number_format($item->iva,2):number_format($item->ieps,2)}}</td>
                <td>$ {{number_format(($item->unit_price*$value->cant),2)}}</td>
                <td>$ {{ number_format($value->total_descuento, 2) }}</td>
                <td>$ {{number_format(((($item->unit_price * $value->cant) - $value->total_descuento) + $impuestos), 2)}}</td>
                @if($sale->amount_received == 0) 
                <td>
                    <button type="button" class="btn btn-info btn-sm" onClick="btnCantProduct({{$item->getPartToProduct->id}})"><i class="fa fa-plus"></i></button>
                    <button type="button" class="btn btn-danger btn-sm" onClick="btnDestroyProduct({{$value->id}})"><i class="fa fa-trash"></i></button>
                </td>
                @endif
            </tr>
            @endforeach
            @endif
            @empty
            <tr id="trEmpty"><td colspan="10" class="table-warning text-center">Sin movimientos.</td></tr>
            @endforelse
        </tbody>
        <tbody id="tbody_total">
            <tr class="table-info"><td colspan="6"></td>
                <td class="text-right text-bold">Totales</td>
                <td class="text-center text-bold" >$ <span id="total_desc" class="badge badge-success">{{number_format($total_desc_, 2)}}</span></td>
                <td class="text-center text-bold" >$ <span id="total_sale">{{number_format($total_sale_, 2)}}</span></td>
                @if($sale->amount_received == 0)
                <td></td>
                @endif
            </tr>
        </tbody>
    </table>
    </div>
</div>