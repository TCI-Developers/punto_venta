<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
     @include('style')
</head>
<body class="body-default">
    <div class="container">
        <div class="box box-5 text-left">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" class="thumb">
            @endif
        </div>
        <div class="box box-5 text-right">
             <h3 class="text-center mt-15">Reporte de Inventario</h3>
                <p class="text text-md text-bold">Fecha: {{$startDate}} a {{$endDate}}</p>
        </div>
    </div>

    <table class="table mt-15">
        <thead>
            <tr>
                <th class="th">Codigo del Producto</th>
                <th class="th">Descripción</th>
                <th class="th">Línea</th>
                <th class="th">Existencia Inicial</th>
                <th class="th">Total Entrada</th>
                <th class="th">Total Salida</th>
                <th class="th">Existencia Real</th>
                <th class="th">Costo U</th>
                <th class="th">Costo Inventario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos ?? [] as $producto)
                <tr>
                    <td class="td text-center">{{ $producto['codigo'] }}</td>
                    <td class="td">{{ $producto['descripcion'] }}</td>
                    <td class="td text-center">{{ $producto['linea'] }}</td>
                    <td class="td text-right">{{ number_format($producto['existencia_inicial'], 2) }}</td>
                    <td class="td text-right">{{ number_format($producto['total_entrada'], 2) }}</td>
                    <td class="td text-right">{{ number_format($producto['total_salida'], 2) }}</td>
                    <td class="td text-right">{{ number_format($producto['existencia_real'], 2) }}</td>
                    <td class="td text-right">${{ number_format($producto['costo_u'], 2) }}</td>
                    <td class="td text-right">${{ number_format($producto['costo_inventario'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
