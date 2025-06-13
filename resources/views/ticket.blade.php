<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ticket de Venta</title>
    @include('styles-ticket')
</head>
<body>
    <div class="ticket-container">
        <!-- Encabezado -->
        <div class="header">
            <div><strong>{{$empresa->razon_social}}</strong></div>
            <div>RFC: {{$empresa->rfc}}</div>
            <div>{{$empresa->getBranch->address}}</div>
        </div>

        <!-- Info Venta -->
        <div class="info-venta">
            <div><strong>Ticket:</strong> {{$sale->folio}}</div>
            <div><strong>Fecha:</strong> {{date('d-m-Y', strtotime($sale->date))}}</div>
            <div><strong>Cliente:</strong> {{$sale->getClient->name}}</div>
            <div><strong>Atendió:</strong> {{$sale->getUser->name}}</div>
        </div>

        <!-- Productos -->
        <table class="table">
            <thead>
                <tr>
                    <th>Cant.</th>
                    <th>Descripción</th>
                    <th class="text-right">P.Unit</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->getDetails ?? [] as $item)
                <tr>
                    <td>{{ number_format($item->getCantSalesDetail->sum('cant'),2) }}</td>
                    <td>{{ $item->getPartToProduct->getProduct->description }}</td>
                    <td class="text-right">$ {{ $item->unit_price }}</td>
                    <td class="text-right">$ {{  number_format( ($item->getCantSalesDetail->sum('cant')*$item->unit_price ) ,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="text-right">
            <div>Subtotal: $ {{$sale->getDetailsTotales('subtotal')}}</div>
            <div>IVA: $ {{number_format($sale->getDetailsTotales('iva'),2)}}</div>
            @if($sale->getDetailsTotales('ieps') > 0)
            <div>IEPS: $ {{number_format($sale->getDetailsTotales('ieps'),2)}}</div>
            @endif
            <div class="total">TOTAL: $ {{number_format($sale->getDetailsTotales('total'),2)}}</div>
        </div>

        <!-- Método de Pago -->
        <div class="info-venta">
            <div><strong>Método de pago:</strong> {{$sale->type_payment}}</div>
            <div><strong>Efectivo:</strong> $ {{number_format($sale->amount_received, 2)}}</div>
            <div><strong>Cambio:</strong> $ {{number_format($sale->change, 2)}}</div>
        </div>

        <!-- Pie -->
        <div class="footer">
            <div>¡Gracias por su compra!</div>
            <div>{{$empresa->razon_social}}</div>
            <div>-- Este ticket no es válido como factura --</div>
        </div>
    </div>
</body>
</html>