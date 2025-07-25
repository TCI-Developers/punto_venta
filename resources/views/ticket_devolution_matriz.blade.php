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
            <div><strong>Folio:</strong> {{$compra->folio}}</div>
            <div><strong>Fecha Devolución:</strong> {{date('d-m-Y', strtotime($devolucion->date))}}</div>
            <div><strong>Cliente:</strong> {{$compra->user}}</div>
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
                <tr>
                    <td>{{ number_format($devolucion->cantidad,2) }}</td>
                    <td width="110">{{ $devolucion->getProduct->description }}</td>
                    <td class="text-right">$ {{ number_format(($devolucion->subtotal / $devolucion->cantidad),2) }}</td>
                    <td class="text-right">$ {{  number_format($devolucion->subtotal ,2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totales -->
        <div class="text-right">
            <div>Subtotal: $ {{number_format($devolucion->subtotal, 2)}}</div>
            <div>Impuesto: $ {{number_format($devolucion->total_impuesto,2)}}</div>
             @if($devolucion->descuento > 0)
             <div>Descuento: $ {{number_format($devolucion->descuento ,2)}}</div>
             @endif
            <div class="total">TOTAL DEVOLUCIÓN: $ {{number_format($devolucion->total,2)}}</div>
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