<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ticket De Devolución</title>
    @include('styles-ticket')
</head>
<body>
    <div class="ticket-container">
        <!-- Encabezado -->
        <div class="header">
            <div>
                <img src="{{$logoBase64}}" alt="logo" width="70">
            </div>
            <div><strong>{{$empresa->razon_social}}</strong></div>
            <div>RFC: {{$empresa->rfc}}</div>
            <div>{{$empresa->getBranch->address}}</div>
        </div>

        <!-- Info Venta -->
        <div class="info-venta">
            <div><strong>Folio:</strong> {{$sale->folio}}</div>
            <div><strong>Fecha Devolución:</strong> {{date('d-m-Y', strtotime($devolucion->fecha_devolucion))}}</div>
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
                @php $subtotal = 0; $descuento = 0; @endphp
                @foreach($products ?? [] as $item)
                    @foreach($item->getCantSalesDetailDev ?? [] as $cantDetails)
                    @php
                        $price =  $item->unit_price - $cantDetails->descuento ?? 0;
                        $price_total = ($cantDetails->cant * $price);
                        $descuento += $cantDetails->cant * $cantDetails->descuento ?? 0; 
                        $subtotal += $price_total;
                    @endphp
                    <tr>
                        <td>{{ number_format($cantDetails->cant,2) }}</td>
                        <td width="110">{{ $item->getPartToProduct->getProduct->description }}</td>
                        <td class="text-right">$ {{ number_format($price,2) }}</td>
                        <td class="text-right">$ {{  number_format($price_total ,2) }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="text-right">
            <div>Subtotal: $ {{number_format($subtotal, 2)}}</div>
            <div>IVA: $ {{number_format($sale->getDetailsDevTotales('iva'),2)}}</div>
             @if($descuento > 0)
             <div>Usted ahorró: $ {{number_format($descuento ,2)}}</div>
             @endif
            @if($sale->getDetailsDevTotales('ieps') > 0)
            <div>IEPS: $ {{number_format($sale->getDetailsDevTotales('ieps'),2)}}</div>
            @endif
            <div class="total">TOTAL DEVOLUCIÓN: $ {{number_format($subtotal + $sale->getDetailsDevTotales('iva') + $sale->getDetailsDevTotales('ieps'),2)}}</div>
        </div>

        <!-- Método de Pago -->
        <div class="info-venta">
            <div><strong>Método de reembolso:</strong> Efectivo</div>
            <div><strong>Efectivo entregado:</strong> $ {{number_format($devolucion->total_devolucion, 2)}}</div>
        </div>

        <!-- Pie -->
        <div class="footer">
            <div>¡Gracias por su preferencia!</div>
            <div>{{$empresa->razon_social}}</div>
            <div>-- Este ticket no es válido como factura --</div>
        </div>
    </div>
</body>
</html>