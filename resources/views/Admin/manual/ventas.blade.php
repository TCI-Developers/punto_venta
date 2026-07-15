<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual — Módulo de Ventas</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <style>
        .manual-section {
            border-left: 4px solid #28a745;
            padding-left: 18px;
            margin-bottom: 36px;
        }
        .manual-section h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .paso-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #28a745;
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
            margin-right: 10px;
            flex-shrink: 0;
        }
        .paso-titulo {
            display: flex;
            align-items: center;
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .screenshot-wrap {
            background: #f8f9fa;
            border: 2px dashed #ced4da;
            border-radius: 8px;
            padding: 14px;
            margin: 12px 0 6px;
            text-align: center;
        }
        .screenshot-wrap img {
            max-width: 100%;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,.15);
        }
        .screenshot-placeholder {
            color: #adb5bd;
            font-size: .85rem;
            padding: 30px 0;
        }
        .screenshot-placeholder i { font-size: 2rem; display: block; margin-bottom: 6px; }
        .nota-info {
            background: #e8f4fd;
            border-left: 4px solid #17a2b8;
            padding: 10px 14px;
            border-radius: 0 6px 6px 0;
            font-size: .88rem;
            margin: 10px 0;
        }
        .nota-warning {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 10px 14px;
            border-radius: 0 6px 6px 0;
            font-size: .88rem;
            margin: 10px 0;
        }
        .toc a { color: #495057; text-decoration: none; font-size: .9rem; }
        .toc a:hover { color: #28a745; text-decoration: underline; }
        .toc li { margin-bottom: 4px; }
        @media print {
            .main-sidebar, header, .btn-volver { display: none !important; }
            .manual-section { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')

        <div class="card card-primary">
            <div class="card-header with-border">
                <h2>
                    <a href="{{ route('manual.index') }}" class="btn btn-success float-left btn-sm btn-volver"
                       data-toggle="tooltip" title="Regresar al manual">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <i class="fa fa-cart-plus"></i> Manual — Módulo de Ventas
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm float-right">
                        <i class="fa fa-print"></i> Imprimir
                    </button>
                </h2>
            </div>
            <div class="card-body">
                <div class="row">

                    {{-- ── Índice de contenido ─────────────────── --}}
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <strong><i class="fa fa-list"></i> Contenido</strong>
                            </div>
                            <div class="card-body py-2 px-3">
                                <ul class="toc list-unstyled mb-0">
                                    <li><a href="#intro">Introducción</a></li>
                                    <li><a href="#paso1">1. Iniciar una venta</a></li>
                                    <li><a href="#paso2">2. Formulario de nueva venta</a></li>
                                    <li><a href="#paso3">3. Agregar productos</a></li>
                                    <li><a href="#paso4">4. Revisar totales y guardar</a></li>
                                    <li><a href="#paso5">5. Completar la venta</a></li>
                                    <li><a href="#paso6">6. Ticket y facturación</a></li>
                                    <li><a href="#historial">Consultar historial</a></li>
                                    <li><a href="#devoluciones">Devoluciones</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- ── Contenido principal ─────────────────── --}}
                    <div class="col-md-9">

                        {{-- Introducción --}}
                        <div class="manual-section" id="intro">
                            <h4><i class="fa fa-info-circle text-success"></i> Introducción</h4>
                            <p>
                                El módulo de <strong>Ventas</strong> es el núcleo del sistema POS. Desde aquí
                                puedes registrar cada venta, cobrar al cliente, imprimir o enviar el ticket
                                y, si el cliente requiere factura, iniciar el proceso de facturación CFDI 4.0
                                directamente desde el ticket.
                            </p>
                            <div class="nota-info">
                                <i class="fa fa-lock"></i> <strong>Acceso:</strong>
                                Para utilizar este módulo necesitas el permiso <em>ventas → crear</em>
                                asignado a tu rol. Consulta con el administrador si no ves las opciones.
                            </div>
                        </div>

                        {{-- Paso 1 --}}
                        <div class="manual-section" id="paso1">
                            <div class="paso-titulo">
                                <span class="paso-num">1</span> Iniciar una nueva venta
                            </div>
                            <p>
                                En el menú lateral haz clic en <strong>Ventas</strong>.
                                Verás el listado de todas las ventas registradas en el sistema,
                                con columnas de usuario, folio, fecha, método de pago, monto y
                                última actualización.
                            </p>
                            <p>
                                Para registrar una nueva venta haz clic en el botón
                                <span style="display:inline-flex;align-items:center;gap:4px;">
                                    <span style="background:#28a745;color:#fff;border-radius:4px;padding:1px 8px;font-weight:700;font-size:1rem;">+</span>
                                </span>
                                verde que aparece junto al campo <em>Mostrar</em> en la parte
                                superior izquierda de la pantalla.
                            </p>

                            @php $img = public_path('img/manual/ventas/paso-1-nueva-venta.png'); @endphp
                            <div class="screenshot-wrap">
                                @if(file_exists($img))
                                    <img src="{{ asset('img/manual/ventas/paso-1-nueva-venta.png') }}"
                                         alt="Pantalla listado de ventas con botón Nueva Venta">
                                @else
                                    <div class="screenshot-placeholder">
                                        <i class="fa fa-picture-o"></i>
                                        Captura: listado de ventas con botón <em>+ Nueva Venta</em>
                                        <br><small>img/manual/ventas/paso-1-nueva-venta.png</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Paso 2 --}}
                        <div class="manual-section" id="paso2">
                            <div class="paso-titulo">
                                <span class="paso-num">2</span> Formulario de nueva venta
                            </div>
                            <p>
                                Al hacer clic en <strong>+</strong> se abre el formulario de venta.
                                En la parte superior encontrarás cinco campos que debes revisar antes
                                de agregar productos:
                            </p>
                            <ul>
                                <li>
                                    <strong>Cliente</strong> — por defecto es <em>Publico General</em>.
                                    Si el cliente está registrado, selecciónalo desde el desplegable.
                                </li>
                                <li>
                                    <strong>Método de Pago</strong>:
                                    <ul>
                                        <li><strong>PUE</strong> — Pago en Una sola Exhibición (pago inmediato).</li>
                                        <li><strong>PPD</strong> — Pago en Parcialidades o Diferido (pago posterior o en abonos).</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong>Tipo de Pago</strong> — el sistema lo sugiere automáticamente
                                    según el Método de Pago, pero <strong>puedes cambiarlo</strong>:
                                    <table class="table table-sm table-bordered mt-2 mb-1" style="font-size:.85rem;">
                                        <thead class="thead-light">
                                            <tr><th>Opción</th><th>Cuándo usarla</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><strong>Efectivo</strong></td><td>El cliente paga con billetes o monedas. <em>(PUE lo selecciona por defecto)</em></td></tr>
                                            <tr><td><strong>Tarjeta crédito</strong></td><td>El cliente paga con tarjeta de crédito. <em>(PPD lo selecciona por defecto)</em></td></tr>
                                            <tr><td><strong>Tarjeta débito</strong></td><td>El cliente paga con tarjeta de débito.</td></tr>
                                            <tr><td><strong>Transferencia</strong></td><td>El cliente realiza una transferencia bancaria o SPEI.</td></tr>
                                        </tbody>
                                    </table>
                                    <div class="nota-info" style="margin-top:6px;">
                                        <i class="fa fa-info-circle"></i>
                                        Es importante seleccionar el tipo correcto porque al generar la factura
                                        CFDI el sistema lo utiliza para rellenar la <strong>Forma de Pago</strong>
                                        automáticamente. Si el cliente pide la factura días después, no tendrás
                                        que recordarlo.
                                    </div>
                                </li>
                                <li><strong>Moneda</strong> — normalmente MXN.</li>
                                <li><strong>Fecha</strong> — se llena automáticamente con la fecha actual.</li>
                            </ul>

                            @php $img2 = public_path('img/manual/ventas/paso-2-formulario.png'); @endphp
                            <div class="screenshot-wrap">
                                @if(file_exists($img2))
                                    <img src="{{ asset('img/manual/ventas/paso-2-formulario.png') }}"
                                         alt="Formulario de nueva venta con campos de cabecera">
                                @else
                                    <div class="screenshot-placeholder">
                                        <i class="fa fa-picture-o"></i>
                                        Captura: formulario de nueva venta con campos Cliente, Método de Pago, Tipo de Pago, Moneda y Fecha
                                        <br><small>img/manual/ventas/paso-2-formulario.png</small>
                                    </div>
                                @endif
                            </div>

                            <div class="nota-info">
                                <i class="fa fa-pencil"></i> <strong>Habilitar Edición:</strong>
                                Si necesitas modificar los campos de cabecera después de haber
                                agregado productos, haz clic en el botón azul <em>Habilitar Edición</em>
                                que aparece en la esquina superior derecha.
                            </div>
                        </div>

                        {{-- Paso 3 --}}
                        <div class="manual-section" id="paso3">
                            <div class="paso-titulo">
                                <span class="paso-num">3</span> Agregar productos
                            </div>
                            <p>
                                Debajo de los campos de cabecera encontrarás el buscador de productos.
                                Escribe el nombre o código en el campo <strong>Presentación</strong>
                                y selecciona el producto de la lista que aparece.
                                El artículo se agregará a la tabla con su precio, impuesto y subtotal.
                            </p>
                            <p>
                                Si necesitas registrar un producto que no está en el catálogo,
                                usa el botón <strong class="text-info">Producto Manual</strong>
                                para capturarlo directamente con precio y descripción libres.
                            </p>
                            <p>
                                La tabla muestra por cada línea:
                                <strong>Producto, Salida, Unidad, Tipo Impuesto, Precio Unitario,
                                Importe Impuesto, Subtotal, Descuento, Total</strong> y botones de acción.
                            </p>

                            @php $img3 = public_path('img/manual/ventas/paso-3-productos.png'); @endphp
                            <div class="screenshot-wrap">
                                @if(file_exists($img3))
                                    <img src="{{ asset('img/manual/ventas/paso-3-productos.png') }}"
                                         alt="Buscador de presentación y tabla de productos agregados">
                                @else
                                    <div class="screenshot-placeholder">
                                        <i class="fa fa-picture-o"></i>
                                        Captura: campo Presentación con productos agregados en la tabla
                                        <br><small>img/manual/ventas/paso-3-productos.png</small>
                                    </div>
                                @endif
                            </div>

                            <div class="nota-info">
                                <i class="fa fa-tag"></i> <strong>Descuentos:</strong>
                                Si un producto tiene descuento configurado, aparece automáticamente
                                en la columna <em>Descuento</em> de la tabla.
                            </div>
                        </div>

                        {{-- Paso 4 (antes era paso 3 de cliente — ahora integrado en paso 2) --}}
                        <div class="manual-section" id="paso4">
                            <div class="paso-titulo">
                                <span class="paso-num">4</span> Revisar totales y guardar
                            </div>
                            <p>
                                Al pie de la tabla aparece la fila de <strong>Totales</strong>
                                con el importe acumulado de todos los productos.
                                Verifica que el monto sea correcto antes de continuar.
                            </p>
                            <p>
                                Una vez que todos los productos están en la lista y los datos de
                                cabecera son correctos, haz clic en el botón
                                <strong class="text-success">Guardar</strong> para registrar la venta
                                en el sistema.
                            </p>

                            @php $img4 = public_path('img/manual/ventas/paso-4-totales.png'); @endphp
                            <div class="screenshot-wrap">
                                @if(file_exists($img4))
                                    <img src="{{ asset('img/manual/ventas/paso-4-totales.png') }}"
                                         alt="Fila de totales al pie de la tabla de productos">
                                @else
                                    <div class="screenshot-placeholder">
                                        <i class="fa fa-picture-o"></i>
                                        Captura: tabla con productos y fila de totales al fondo
                                        <br><small>img/manual/ventas/paso-4-totales.png</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Paso 5 --}}
                        <div class="manual-section" id="paso5">
                            <div class="paso-titulo">
                                <span class="paso-num">5</span> Completar la venta
                            </div>
                            <p>
                                Una vez verificados los productos y la forma de pago, haz clic en
                                el botón <strong class="text-success">Cobrar</strong>.
                                El sistema registrará la venta y mostrará un resumen con el
                                <strong>cambio a entregar</strong> (si aplica).
                            </p>

                            @php $img5 = public_path('img/manual/ventas/paso-5-cobrar.png'); @endphp
                            <div class="screenshot-wrap">
                                @if(file_exists($img5))
                                    <img src="{{ asset('img/manual/ventas/paso-5-cobrar.png') }}"
                                         alt="Confirmación de venta completada con monto de cambio">
                                @else
                                    <div class="screenshot-placeholder">
                                        <i class="fa fa-picture-o"></i>
                                        Captura: pantalla de confirmación con cambio y botón Cobrar
                                        <br><small>img/manual/ventas/paso-5-cobrar.png</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Paso 6 --}}
                        <div class="manual-section" id="paso6">
                            <div class="paso-titulo">
                                <span class="paso-num">6</span> Ticket y facturación
                            </div>
                            <p>
                                Al completar la venta aparece el modal del <strong>ticket</strong>.
                                Desde ahí tienes dos opciones:
                            </p>
                            <ul>
                                <li>
                                    <strong>Cerrar</strong> — cierra el ticket y regresa al listado de ventas.
                                </li>
                                <li>
                                    <strong><i class="fa fa-file-text-o"></i> Facturar</strong>
                                    — te lleva directamente al formulario de facturación CFDI 4.0
                                    con los datos de la venta precargados.
                                    <a href="{{ route('manual.show', 'facturas') }}" class="text-muted">
                                        <small>(Ver manual de Facturas cuando esté disponible)</small>
                                    </a>
                                </li>
                            </ul>

                            @php $img6 = public_path('img/manual/ventas/paso-6-ticket.png'); @endphp
                            <div class="screenshot-wrap">
                                @if(file_exists($img6))
                                    <img src="{{ asset('img/manual/ventas/paso-6-ticket.png') }}"
                                         alt="Modal del ticket con botones Cerrar y Facturar">
                                @else
                                    <div class="screenshot-placeholder">
                                        <i class="fa fa-picture-o"></i>
                                        Captura: modal del ticket con botones <em>Cerrar</em> y <em>Facturar</em>
                                        <br><small>img/manual/ventas/paso-6-ticket.png</small>
                                    </div>
                                @endif
                            </div>

                            <div class="nota-warning">
                                <i class="fa fa-exclamation-triangle"></i> <strong>Nota:</strong>
                                El botón <em>Facturar</em> solo aparece si tienes permiso de
                                <em>facturas → crear</em>. Si no lo ves, solicítalo al administrador.
                            </div>
                        </div>

                        {{-- Historial --}}
                        <div class="manual-section" id="historial">
                            <h4><i class="fa fa-history text-success"></i> Consultar historial de ventas</h4>
                            <p>
                                En el menú lateral haz clic en <strong>Ventas</strong>.
                                El listado muestra todas las ventas del sistema con filtros por
                                fecha, folio, cliente y estado.
                                Haz clic en el ícono de <i class="fa fa-eye"></i> <strong>Ver</strong>
                                para consultar el detalle completo de una venta:
                                productos, importes, forma de pago y factura asociada (si existe).
                            </p>

                            @php $imgH = public_path('img/manual/ventas/historial-ventas.png'); @endphp
                            <div class="screenshot-wrap">
                                @if(file_exists($imgH))
                                    <img src="{{ asset('img/manual/ventas/historial-ventas.png') }}"
                                         alt="Listado de ventas con filtros">
                                @else
                                    <div class="screenshot-placeholder">
                                        <i class="fa fa-picture-o"></i>
                                        Captura: listado de ventas con filtros y columnas
                                        <br><small>img/manual/ventas/historial-ventas.png</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Devoluciones --}}
                        <div class="manual-section" id="devoluciones">
                            <h4><i class="fa fa-refresh text-success"></i> Devoluciones</h4>
                            <p>
                                Si un cliente devuelve un producto, ingresa al detalle de la venta
                                original y haz clic en el botón <strong>Devolución</strong>.
                                El sistema te permite seleccionar los artículos a devolver y
                                la cantidad, y genera un registro de devolución vinculado a la venta.
                            </p>
                            <div class="nota-warning">
                                <i class="fa fa-exclamation-triangle"></i> <strong>Importante:</strong>
                                Las devoluciones no cancelan automáticamente el CFDI.
                                Si la venta tenía factura emitida, deberás cancelarla por separado
                                desde el módulo de <strong>Facturas</strong>.
                            </div>

                            @php $imgD = public_path('img/manual/ventas/devoluciones.png'); @endphp
                            <div class="screenshot-wrap">
                                @if(file_exists($imgD))
                                    <img src="{{ asset('img/manual/ventas/devoluciones.png') }}"
                                         alt="Pantalla de devolución con selección de productos">
                                @else
                                    <div class="screenshot-placeholder">
                                        <i class="fa fa-picture-o"></i>
                                        Captura: pantalla de devolución con selección de artículos
                                        <br><small>img/manual/ventas/devoluciones.png</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>{{-- /col-md-9 --}}
                </div>{{-- /row --}}
            </div>
        </div>
    </main>
</body>
</html>
