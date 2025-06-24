<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Requisición</title>
    @include('style')
</head>
<body>
    <!-- Logo, Datos compañia y fecha -->
    <div class="container">
        <div class="box box-3">
            <img src="{{ $logoBase64 }}" alt="logo" width="170">
        </div>
        <div class="box box-3 mt-30">
            <p class="text text-lg text-left text-bold">Pequeñita</p>
            <p class="text text-md text-left">Dirección sucursal</p>
            <p class="text text-md text-left">Uruapan, Mich.</p>
        </div>
        <div class="box box-3 mt-30">
            <div class="card">
                <div class="card-header">
                    FOLIO
                </div>
                <div class="card-body">
                    {{$compra->folio ?? ''}} 
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    PROGRAMACIÓN ENTREGA
                </div>
                <div class="card-body">
                    {{date('d-m-Y', strtotime($compra->programacion_entrega)) ?? ''}}
                </div>
            </div>
        </div>
    </div>

    <!-- proveedor -->
    <div class="container">
        <div class="box box-10">
            <div class="card">
                <div class="card-header">
                   PROVEEDOR:
                </div>
                <div class="card-body h-20">
                    {{$compra->getProveedor->name ?? ''}}
                </div>
            </div>
        </div>
    </div>

    <!-- Tractor, Modelo y Color -->
    <div class="container">
        <div class="box box-3">
            <div class="card mr-5">
                <div class="card-header">
                   MONEDA:
                </div>
                <div class="card-body h-20">
                    {{$compra->moneda}}
                </div>
            </div>
        </div>
        <div class="box box-3">
            <div class="card mr-5">
                <div class="card-header">
                    PLAZO:
                </div>
                <div class="card-body h-20">
                    {{$compra->plazo}}
                </div>
            </div>
        </div>
        <div class="box box-3">
            <div class="card">
                <div class="card-header">
                   TIPO DE COMPRA:
                </div>
                <div class="card-body h-20">
                    {{$compra->tipo == 'OC' ? 'Orden de compra':'Requisición'}}
                </div>
            </div>
        </div>
    </div>

    <!-- Número de Orden -->
    <div class="container">
        <div class="box box-10">
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="th text-sm">Codigo</th>
                            <th class="th text-sm">Descripción</th>
                            <th class="th text-sm">Entrada</th>
                            <th class="th text-sm">Precio Unitario</th>
                            <th class="th text-sm">Subtotal</th>
                            <th class="th text-sm">Impuestos</th>
                            <th class="th text-sm">Valor Impuesto</th>
                            <th class="th text-sm">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($compra->getDetalles ?? [] as $item)
                        <tr>
                            <td class="td text-xs text-center">{{$item->getProduct->code_product}}</td>
                            <td class="td text-xs">{{$item->descripcion_producto}}</td>
                            <td class="td text-xs text-center">{{$item->getEntrada->entrada}}</td>
                            <td class="td text-xs text-right">$ {{number_format($item->precio_unitario, 2)}}</td>
                            <td class="td text-xs text-right">$ {{number_format($item->subtotal, 2)}}</td>
                            <td class="td text-xs text-center">{{$item->taxes}}</td>
                            <td class="td text-xs text-right">$ {{$item->impuestos}}</td>
                            <td class="td text-xs text-right">$ {{number_format($item->total, 2)}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>