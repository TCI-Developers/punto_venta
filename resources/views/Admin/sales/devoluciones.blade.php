<div class="col-lg-12 col-md-12 col-sm-12">
    <div class="table-responsive">
    <table class="table table-striped table-bordered table-secondary">
        <thead>
            <tr>
                <th colspan="6" class="text-center">DEVOLUCIONES</th>
            </tr>
            <tr class="text-center">
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
                <th>Descuento</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales_detail_dev as $detail)
                <tr class="text-center">
                    <td>{{ $detail->getPartToProduct->getProduct->code_product ?? '' }}</td>
                    <td>{{ $detail->getCantSalesDetailDev[0]->cant ?? '' }}</td>
                    <td>${{ number_format($detail->unit_price, 2) }}</td>
                    <td>${{ number_format($detail->subtotal, 2) }}</td>
                    <td>${{ number_format($detail->getCantSalesDetailDev[0]->total_descuento, 2) }}</td>
                    <td>${{ number_format(($detail->total - $detail->getCantSalesDetailDev[0]->total_descuento), 2) }}</td>
                </tr>
            @empty
            @endforelse

            @forelse($devoluciones as $item)
                <tr class="text-center table-dev">
                    <th colspan="2">Fecha: {{date('d-m-Y', strtotime($item->fecha_devolucion))}}</th>
                    <th colspan="2">Cantidad Total: {{$item->cantidad}}</th>
                    <th colspan="2">Total Devolución: ${{$item->total_devolucion - $item->total_descuentos}}</th>
                </tr>
            @empty
                <tr id="trEmpty"><td colspan="6" class="table-warning text-center">Sin devoluciones.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>