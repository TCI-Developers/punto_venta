<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ticket de Venta</title>
    @include('styles-ticket')
</head>
<body>
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

        <!-- Titulo Cierre de Turno -->
        <div class="info-venta" style="text-align: center; margin: 10px 0;">
            <strong>CIERRE DE TURNO</strong>
        </div>

        <!-- Info Vendedor -->
        <div class="info-venta">
            <div><strong>Vendedor:</strong> {{$user->name}}</div>
            <div><strong>Turno:</strong> {{$user->getTurno->turno ?? ''}}</div>
            <div><strong>Fecha/Hora Cierre:</strong> {{$user->getTurno->entrada ?? ''}} - {{$user->getTurno->salida ?? ''}}</div>
        </div>

        <!-- Denominación de Billetes -->
        <div class="denominaciones">
            <div><strong>Denominaciones en Efectivo:</strong></div>
            <div class="denominacion-item"><span>{{$box->ticket_1000 > 0 ? $box->ticket_1000.' x $1000':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->ticket_500 > 0 ? $box->ticket_500.' x $500':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->ticket_200 > 0 ? $box->ticket_200.' x $200':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->ticket_100 > 0 ? $box->ticket_100.' x $100':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->ticket_50 > 0 ? $box->ticket_50.' x $50':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->ticket_20 > 0 ? $box->ticket_20.' x $20':''}}</span> </div>
            
            <div class="denominacion-item"><span>{{$box->coin_20 > 0 ? $box->coin_20.' x $20':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->coin_10 > 0 ? $box->coin_10.' x $10':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->coin_5 > 0 ? $box->coin_5.' x $5':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->coin_2 > 0 ? $box->coin_2.' x $2':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->coin_1 > 0 ? $box->coin_1.' x $1':''}}</span> </div>
            <div class="denominacion-item"><span>{{$box->coin_50_cen > 0 ? $box->coin_50_cen.' x $.50':''}}</span> </div>
        </div>

        <!-- Resumen de Ventas -->
        <div class="resumen-turno" style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px;">
            <div><strong>Total en Efectivo:</strong> $ {{number_format($box->amount_cash_user, 2)}}</div>
            <div><strong>Total con Tarjeta:</strong> $ {{number_format($box->amount_credit_user, 2)}}</div>
            <div><strong>Ventas Totales:</strong> {{$number_ventas}}</div>
        </div>

        <!-- Resumen Final -->
        <div style="margin-top: 10px;">
            <div><strong>Total Ingresos:</strong> $ {{number_format($box->amount_cash_user + $box->amount_credit_user, 2)}} (Efectivo + Tarjeta)</div>
        </div>

        <!-- Pie -->
        <div class="footer">
            <div>¡Gracias por tu trabajo!</div>
            <div>{{$empresa->razon_social}}</div>
            <div>-- Cierre de turno registrado correctamente --</div>
        </div>
    </div>
</body>
</html>