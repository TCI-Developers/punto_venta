<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ticket de Venta</title>
    @include('styles-ticket')
</head>
<body>
    <body>
    @php
        $devoluciones_turno = $box->getTotalDevolutions($box->start_date, $box->end_date);
        $total_ingresos = $box->start_amount_box + $box->amount_cash_system;
        $total_egresos = $devoluciones_turno + $box->total_gastos;
        $total_en_caja = $total_ingresos - $total_egresos;
        $retiro_caja = $box->amount_cash_user - $box->monto_dejado_caja;
        $diferencia = $total_en_caja - $box->amount_cash_user;
    @endphp
    <div class="ticket-container">
        <!-- Encabezado -->
        <div class="header">
            <img src="{{$logoBase64}}" alt="logo" width="70" height="45">
            <div><strong>{{$empresa->razon_social}}</strong></div>
            <div>RFC: {{$empresa->rfc}}</div>
            <div>{{$empresa->getBranch->address}}</div>
        </div>

        <!-- Titulo Cierre de Turno -->
        <div class="info-venta" style="text-align: center; margin: 10px 0;">
            <strong>CIERRE DE TURNO</strong><br>
            Corte # {{$box->id}}
        </div>

        <!-- Info Vendedor -->
        <div class="info-venta">
            <div><strong>Vendedor:</strong> {{$user->name}}</div>
            <div><strong>Turno:</strong> {{$user->getTurno->turno ?? ''}}</div>
            <div><strong>Fecha/Hora Cierre:</strong> {{$user->getTurno->entrada ?? ''}} - {{$user->getTurno->salida ?? ''}}</div>
        </div>

        <!-- Ingresos -->
        <div class="info-venta">
            <div class="text-center"><strong>** INGRESOS **</strong></div>
            <div>Caja inicial: $ {{number_format($box->start_amount_box, 2)}}</div>
            <div>Ventas en efectivo: $ {{number_format($box->amount_cash_system, 2)}}</div>
            <div><strong>Total de ingresos: $ {{number_format($total_ingresos, 2)}}</strong></div>
        </div>

        <!-- Egresos -->
        <div class="info-venta">
            <div class="text-center"><strong>** EGRESOS **</strong></div>
            <div>Devoluciones: $ {{number_format($devoluciones_turno, 2)}}</div>
            <div>Gastos (varios): $ {{number_format($box->total_gastos, 2)}}</div>
            <div><strong>Total de egresos: $ {{number_format($total_egresos, 2)}}</strong></div>
        </div>

        <div class="info-venta text-center total">
            TOTAL EN CAJA: $ {{number_format($total_en_caja, 2)}}
        </div>

        <!-- Folios de documentos del turno (referencia interna, no folio fiscal) -->
        <div class="info-venta">
            <div class="text-center"><strong>** FOLIOS DE DOCUMENTOS **</strong></div>
            <div>Folio ticket inicial: {{$folio_venta_inicial ? 'R-'.$folio_venta_inicial : 'N/A'}}</div>
            <div>Folio ticket final: {{$folio_venta_final ? 'R-'.$folio_venta_final : 'N/A'}}</div>
            <div>Folio factura inicial: {{$folio_factura_inicial ? 'FAC-'.$folio_factura_inicial : 'Sin facturas'}}</div>
            <div>Folio factura final: {{$folio_factura_final ? 'FAC-'.$folio_factura_final : 'Sin facturas'}}</div>
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

        <!-- Resumen Arqueo de Caja -->
        <div class="resumen-turno" style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px;">
            <div class="text-center"><strong>** ARQUEO DE CAJA **</strong></div>
            <div><strong>Efectivo contado:</strong> $ {{number_format($box->amount_cash_user, 2)}}</div>
            <div><strong>Tarjeta contada:</strong> $ {{number_format($box->amount_credit_user, 2)}}</div>
            <div><strong>Fondo (caja inicial):</strong> $ {{number_format($box->start_amount_box, 2)}}</div>
            <div><strong>Total arqueo:</strong> $ {{number_format($box->amount_cash_user + $box->amount_credit_user, 2)}}</div>
            <div><strong>Retiro de caja:</strong> $ {{number_format($retiro_caja, 2)}}</div>
            <div><strong>Se deja en caja:</strong> $ {{number_format($box->monto_dejado_caja, 2)}}</div>
            <div><strong>Diferencia:</strong> {{$diferencia < 0 ? '+':($diferencia > 0 ? '-':'')}} $ {{number_format(abs($diferencia), 2)}}</div>
            <div><strong>Clientes atendidos:</strong> {{$number_ventas}}</div>
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