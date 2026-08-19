<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ticket de Gasto</title>
    @include('styles-ticket')
</head>
<body>
    <div class="ticket-container">
        <!-- Encabezado -->
        <div class="header">
            <div>
                <img src="{{$logoBase64}}" alt="logo" width="70" height="45">
            </div>
            <div><strong>{{$empresa->razon_social}}</strong></div>
            <div>RFC: {{$empresa->rfc}}</div>
            <div>{{$empresa->getBranch->address}}</div>
        </div>

        <!-- Titulo -->
        <div class="info-venta" style="text-align: center; margin: 10px 0;">
            <strong>COMPROBANTE DE GASTO DE CAJA</strong>
        </div>

        <!-- Info Gasto -->
        <div class="info-venta">
            <div><strong>Folio:</strong> {{$gasto->id}}</div>
            <div><strong>Fecha:</strong> {{$gasto->created_at->format('d/m/Y H:i')}}</div>
            <div><strong>Registrado por:</strong> {{$gasto->getUser->name ?? ''}}</div>
        </div>

        <!-- Detalle -->
        <div style="margin-top: 10px;">
            <div><strong>Concepto:</strong> {{$gasto->concepto}}</div>
            @if($gasto->description)
            <div><strong>Notas:</strong> {{$gasto->description}}</div>
            @endif
        </div>

        <!-- Total -->
        <div class="info-venta text-center total" style="margin-top: 10px;">
            TOTAL: $ {{number_format($gasto->monto, 2)}}
        </div>

        <!-- Pie -->
        <div class="footer">
            <div>Este monto se descontó del efectivo en caja.</div>
            <div>{{$empresa->razon_social}}</div>
        </div>
    </div>
</body>
</html>
