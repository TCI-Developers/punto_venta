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
            <div>
                <img src="{{$empresa->path_logo}}" alt="logo" width="70">
            </div>
            <div><strong>{{$empresa->razon_social}}</strong></div>
            <div>RFC: {{$empresa->rfc}}</div>
            <div>{{$empresa->getBranch->address}}</div>
        </div>

        <!-- Info Venta -->
        <div class="info-venta">
            <div><strong>Ticket:</strong> R-0</div>
            <div><strong>Fecha:</strong> {{date('d-m-Y')}}</div>
            <div><strong>Cliente:</strong> Cliente Test</div>
            <div><strong>Atendió:</strong> Nombre User</div>
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
                    <td>10</td>
                    <td width="110">Prueba</td>
                    <td class="text-right">$ 100</td>
                    <td class="text-right">$ 100</td>
                </tr>
            </tbody>
        </table>

        <!-- Totales -->
        <div class="text-right">
            <div>Subtotal: $ 100</div>
            <div>IVA: $ 0.00</div>
            <div class="total">TOTAL: $ 100</div>
        </div>

        <!-- Método de Pago -->
        <div class="info-venta">
            <div><strong>Método de pago:</strong> PPD</div>
            <div><strong>Efectivo</strong> $ 0</div>
            <div><strong>Cambio:</strong> $ 0</div>
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