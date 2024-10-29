<div class="col-lg-12 col-md-12 col-sm-12">
    <div class="table-responsive">
    <table class="table table-striped table-bordered table-info">
        <thead>
            <tr>
                <th colspan="4" class="text-center">DEVOLUCIONES</th>
            </tr>
            <tr class="text-center">
                <th>Fecha</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devoluciones as $item)
            <tr class="text-center">
                <td>{{date('d-m-Y', strtotime($item->fecha_devolucion))}}</td>
                <td>{{$item->getProduct->code_product}}</td>
                <td>{{$item->cantidad}}</td>
                <td>$ {{number_format($item->getPartToProduct->price/$item->cantidad)}}</td>
            </tr>
            @empty
            <tr id="trEmpty"><td colspan="4" class="table-warning text-center">Sin devoluciones.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>