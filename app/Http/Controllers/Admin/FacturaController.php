<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FacturacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Factura, Sale, Customer, EmpresaDetail, PartToProduct, Product};

class FacturaController extends Controller
{
    const RFC_PUBLICO_GENERAL     = 'XAXX010101000';
    const NOMBRE_PUBLICO_GENERAL  = 'PUBLICO EN GENERAL';
    const REGIMEN_PUBLICO_GENERAL = '616';
    const USO_CFDI_PUBLICO_GENERAL = 'S01';
    const CP_PUBLICO_GENERAL      = '99999';

    // ─── Listado ─────────────────────────────────────────────────────────────

    public function index()
    {
        $facturas = Factura::with(['customer', 'user'])->orderBy('id', 'desc')->get();
        return view('Admin.facturas.index', ['facturas' => $facturas]);
    }

    // ─── Crear ───────────────────────────────────────────────────────────────

    public function create($sale_id = null)
    {
        $empresa   = EmpresaDetail::first();
        $customers = Customer::where('status', 1)->orderBy('name')->get();

        $ventas = Sale::with('paymentMethod')
            ->where('status', 2)
            ->whereDoesntHave('facturas', fn ($q) => $q->where('status', 1))
            ->orderBy('id', 'desc')
            ->get();

        $facturasTimbradas = Factura::with('customer')
            ->where('status', 1)
            ->where('is_demo', false)
            ->whereNotNull('uuid')
            ->orderBy('id', 'desc')
            ->get();

        return view('Admin.facturas.create', [
            'empresa'           => $empresa,
            'customers'         => $customers,
            'ventas'            => $ventas,
            'sale_id'           => $sale_id,
            'facturasTimbradas' => $facturasTimbradas,
        ]);
    }

    // ─── Timbrar ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'sale_ids'    => 'required|array|min:1',
            'sale_ids.*'  => 'required|integer|exists:sales,id',
            'uso_cfdi'    => 'required|string|max:4',
            'metodo_pago' => 'required|string|max:3',
            'forma_pago'  => 'required|string|max:3',
        ], [
            'sale_ids.required'    => 'Selecciona al menos una venta.',
            'sale_ids.min'         => 'Selecciona al menos una venta.',
            'uso_cfdi.required'    => 'El uso de CFDI es requerido.',
            'metodo_pago.required' => 'El método de pago es requerido.',
            'forma_pago.required'  => 'La forma de pago es requerida.',
        ]);

        $empresa = EmpresaDetail::first();
        $user    = Auth::user();
        $publico = $request->boolean('publico_general');

        // Validar datos fiscales del emisor
        if (!$empresa?->rfc || !$empresa?->regimen_fiscal || !$empresa?->codigo_postal) {
            return redirect()->back()->withInput()->with('error',
                'Completa los datos fiscales de la empresa (RFC, régimen fiscal y código postal) antes de facturar.');
        }

        // Datos del receptor
        if ($publico) {
            $rfc_receptor     = self::RFC_PUBLICO_GENERAL;
            $nombre_receptor  = self::NOMBRE_PUBLICO_GENERAL;
            $regimen_receptor = self::REGIMEN_PUBLICO_GENERAL;
            $cp_receptor      = self::CP_PUBLICO_GENERAL;
            $uso_cfdi         = self::USO_CFDI_PUBLICO_GENERAL;
            $customer_id      = null;
        } else {
            $customer = Customer::find($request->customer_id);
            if (!is_object($customer)) {
                return redirect()->back()->withInput()->with('error', 'Cliente no encontrado.');
            }
            if (!$customer->rfc || !$customer->regimen_fiscal || !$customer->postal_code) {
                return redirect()->back()->withInput()->with('error',
                    'El cliente no tiene RFC, régimen fiscal o código postal registrado. Actualiza sus datos primero.');
            }
            $rfc_receptor     = $customer->rfc;
            $nombre_receptor  = $customer->razon_social ?? $customer->name;
            $regimen_receptor = $customer->regimen_fiscal;
            $cp_receptor      = $customer->postal_code;
            $uso_cfdi         = $request->uso_cfdi;
            $customer_id      = $customer->id;
        }

        // Obtener y validar ventas
        $sales = Sale::whereIn('id', $request->sale_ids)->where('status', 2)->get();
        if ($sales->count() !== count($request->sale_ids)) {
            return redirect()->back()->withInput()->with('error',
                'Una o más ventas seleccionadas no son válidas.');
        }

        // Construir conceptos y calcular totales
        $conceptos = $this->buildConceptos($sales);
        if (empty($conceptos)) {
            return redirect()->back()->withInput()->with('error',
                'Las ventas seleccionadas no tienen productos. Verifica que estén completas.');
        }
        $subtotal = round(collect($conceptos)->sum('subtotal'), 2);
        $iva      = round(collect($conceptos)->sum('iva'), 2);
        $total    = round($subtotal + $iva, 2);

        // Armar payload para XML y metadatos del servicio
        $payload = [
            'emisor' => [
                'rfc'            => $empresa->rfc,
                'nombre'         => $empresa->name ?? $empresa->razon_social,
                'regimen_fiscal' => $empresa->regimen_fiscal,
                'codigo_postal'  => $empresa->codigo_postal,
            ],
            'receptor' => [
                'rfc'            => $rfc_receptor,
                'nombre'         => $nombre_receptor,
                'regimen_fiscal' => $regimen_receptor,
                'codigo_postal'  => $cp_receptor,
                'uso_cfdi'       => $uso_cfdi,
            ],
            'comprobante' => [
                'tipo_comprobante' => 'I',
                'metodo_pago'      => $request->metodo_pago,
                'forma_pago'       => $request->forma_pago,
                'moneda'           => $sales->first()->coin ?? 'MXN',
                'subtotal'         => $subtotal,
                'total'            => $total,
            ],
            'conceptos' => $conceptos,
        ];

        // Crear registro pendiente
        $factura                   = new Factura();
        $factura->tipo_comprobante = 'I';
        $factura->customer_id      = $customer_id;
        $factura->branch_id        = $empresa->branch_id;
        $factura->user_id          = $user->id;
        $factura->subtotal         = $subtotal;
        $factura->iva              = $iva;
        $factura->total            = $total;
        $factura->forma_pago       = $request->forma_pago;
        $factura->metodo_pago      = $request->metodo_pago;
        $factura->uso_cfdi         = $uso_cfdi;
        $factura->moneda           = $sales->first()->coin ?? 'MXN';
        $factura->status           = 0;
        $factura->is_demo          = $request->boolean('pre_timbrado');
        $factura->relacionado_uuid = trim($request->input('relacionado_uuid', '')) ?: null;
        $factura->save();

        $factura->sales()->attach($request->sale_ids);

        // Generar layout Dinvbox y enviar al servicio
        $payload['folio']           = $factura->id;
        $payload['demo']            = $request->boolean('pre_timbrado');
        $payload['relacionado_uuid'] = trim($request->input('relacionado_uuid', ''));
        $layout   = $this->buildDinvboxLayout($payload);
        $servicio = app(FacturacionService::class);
        $response = $servicio->timbrar($layout, [
            'id'      => $factura->id,
            'folio'   => 'FAC-' . $factura->id,
            'tipo'    => 'I',
            'concepto' => 'Venta en punto de venta',
        ]);

        if ($response['success'] ?? false) {
            $factura->status        = 1;
            $factura->uuid          = $response['uuid']         ?? null;
            $factura->xml           = $response['xml']          ?? null;
            $factura->pdf_url       = $response['pdf_url']      ?? ($response['xml_url'] ?? null);
            $factura->response_json = json_encode($response);
            $factura->save();

            return redirect()->route('facturas.show', $factura->id)
                ->with('success', 'Factura timbrada con éxito. UUID: ' . $factura->uuid);
        }

        $factura->status        = 3;
        $factura->error_message = $this->mensajeAmigable($response);
        $factura->response_json = json_encode($response);
        $factura->save();

        return redirect()->route('facturas.show', $factura->id)
            ->with('error', 'Error al timbrar: ' . $factura->error_message);
    }

    // ─── Detalle ─────────────────────────────────────────────────────────────

    public function show($id)
    {
        $factura = Factura::with(['customer', 'user', 'sales'])->find($id);
        if (!is_object($factura)) {
            return redirect()->route('facturas.index')->with('error', 'Factura no encontrada.');
        }
        return view('Admin.facturas.show', ['factura' => $factura]);
    }

    // ─── Cancelación ─────────────────────────────────────────────────────────

    public function cancelForm($id)
    {
        $factura = Factura::find($id);
        if (!is_object($factura)) {
            return redirect()->route('facturas.index')->with('error', 'Factura no encontrada.');
        }
        if ((int) $factura->status !== 1) {
            return redirect()->route('facturas.show', $id)->with('error', 'Solo se pueden cancelar facturas timbradas.');
        }

        // Facturas candidatas para sustitutas: primero las que ya relacionan esta, luego el resto
        $sustitutasVinculadas = Factura::with('customer')
            ->where('status', 1)
            ->where('is_demo', false)
            ->where('id', '!=', $factura->id)
            ->where('relacionado_uuid', $factura->uuid)
            ->orderBy('id', 'desc')
            ->get();

        $sustitutasOtras = Factura::with('customer')
            ->where('status', 1)
            ->where('is_demo', false)
            ->where('id', '!=', $factura->id)
            ->where(fn($q) => $q->whereNull('relacionado_uuid')->orWhere('relacionado_uuid', '!=', $factura->uuid))
            ->orderBy('id', 'desc')
            ->get();

        return view('Admin.facturas.cancel', [
            'factura'              => $factura,
            'sustitutasVinculadas' => $sustitutasVinculadas,
            'sustitutasOtras'      => $sustitutasOtras,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'motivo'    => 'required|in:01,02,03,04',
            'foliosust' => 'required_if:motivo,01|nullable|string|max:36',
        ], [
            'motivo.required'    => 'Selecciona el motivo de cancelación.',
            'motivo.in'          => 'Motivo de cancelación inválido.',
            'foliosust.required_if' => 'El folio de la factura sustituta es requerido para el motivo 01.',
        ]);

        $factura = Factura::find($id);
        if (!is_object($factura)) {
            return redirect()->back()->with('error', 'Factura no encontrada.');
        }
        if ((int) $factura->status !== 1) {
            return redirect()->back()->with('error', 'Solo se pueden cancelar facturas timbradas.');
        }

        $servicio = app(FacturacionService::class);
        $response = $servicio->cancelar(
            $factura->uuid,
            $request->motivo,
            $request->foliosust ?? ''
        );

        if ($response['success'] ?? false) {
            $factura->status    = 2;
            $factura->foliosust = ($request->motivo === '01' && $request->foliosust) ? $request->foliosust : null;
            $factura->save();
            return redirect()->route('facturas.show', $factura->id)
                ->with('success', 'Factura cancelada exitosamente.');
        }

        return redirect()->back()
            ->with('error', 'Error al cancelar: ' . $this->mensajeAmigable($response));
    }

    // ─── Consulta estado SAT ─────────────────────────────────────────────────

    public function consultarEstado($id)
    {
        $factura = Factura::find($id);
        if (!is_object($factura) || !$factura->uuid) {
            return redirect()->back()->with('error', 'Factura sin UUID — no se puede consultar.');
        }

        $rfcReceptor = $factura->customer?->rfc ?? self::RFC_PUBLICO_GENERAL;
        $servicio    = app(FacturacionService::class);
        $response    = $servicio->consultarEstado($factura->uuid, $rfcReceptor, (string) $factura->total);

        if ($response['success'] ?? false) {
            $estado = $response['estado'] ?? '—';
            $cancelable = $response['esCancelable'] ?? '—';
            return redirect()->back()
                ->with('success', "Estado SAT: {$estado} | Cancelable: {$cancelable}");
        }

        return redirect()->back()
            ->with('error', 'Error al consultar: ' . ($response['error_message'] ?? 'Sin respuesta'));
    }

    // ─── Layout Dinvbox (INI) ─────────────────────────────────────────────────

    private function buildDinvboxLayout(array $payload): string
    {
        $emisor    = $payload['emisor'];
        $receptor  = $payload['receptor'];
        $comp      = $payload['comprobante'];
        $conceptos = $payload['conceptos'];
        $folio     = $payload['folio'] ?? '';
        $fecha     = now()->format('Y-m-d\TH:i:s');

        $subtotal = number_format((float) $comp['subtotal'], 2, '.', '');
        $total    = number_format((float) $comp['total'], 2, '.', '');
        $totalIva = number_format(collect($conceptos)->sum('iva'), 2, '.', '');

        $lines = [];

        $lines[] = '[ComprobanteFiscalDigital]';
        $lines[] = 'Version=4.0';
        $lines[] = 'Serie=A';
        $lines[] = 'Folio=' . $folio;
        $lines[] = 'Fecha=' . $fecha;
        $lines[] = 'FormaPago=' . $comp['forma_pago'];
        $lines[] = 'NoCertificado=';
        $lines[] = 'CondicionesDePago=';
        $lines[] = 'SubTotal=' . $subtotal;
        $lines[] = 'Moneda=' . $comp['moneda'];
        $lines[] = 'Total=' . $total;
        $lines[] = 'Exportacion=01';
        $lines[] = 'TipoDeComprobante=' . $comp['tipo_comprobante'];
        $lines[] = 'MetodoPago=' . $comp['metodo_pago'];
        $lines[] = 'LugarExpedicion=' . $emisor['codigo_postal'];
        $lines[] = '';

        if ($receptor['rfc'] === self::RFC_PUBLICO_GENERAL) {
            $lines[] = '[InformacionGlobal]';
            $lines[] = 'Periodicidad=04';
            $lines[] = 'Meses=' . now()->format('m');
            $lines[] = 'Año=' . now()->format('Y');
            $lines[] = '';
        }

        $lines[] = '[DatosAdicionales]';
        $lines[] = 'tipoDocumento=' . $comp['tipo_comprobante'];
        $lines[] = 'demo=' . (($payload['demo'] ?? false) ? 'true' : 'false');
        $lines[] = 'observaciones=';
        $lines[] = 'plantillaPDF=clasic';
        $lines[] = 'logotipo=';
        $lines[] = '';

        $lines[] = '[Emisor]';
        $lines[] = 'Rfc=' . $emisor['rfc'];
        $lines[] = 'Nombre=' . $emisor['nombre'];
        $lines[] = 'RegimenFiscal=' . $emisor['regimen_fiscal'];
        $lines[] = '';

        $lines[] = '[Receptor]';
        $lines[] = 'Rfc=' . $receptor['rfc'];
        $lines[] = 'Nombre=' . $receptor['nombre'];
        $lines[] = 'DomicilioFiscalReceptor=' . $receptor['codigo_postal'];
        $lines[] = 'RegimenFiscalReceptor=' . $receptor['regimen_fiscal'];
        $lines[] = 'UsoCFDI=' . $receptor['uso_cfdi'];
        $lines[] = '';

        // [CfdiRelacionados|] — solo cuando esta factura sustituye a otra (cancelación motivo 01)
        $relacionadoUuid = $payload['relacionado_uuid'] ?? '';
        if ($relacionadoUuid !== '') {
            $lines[] = '[CfdiRelacionados|]';
            $lines[] = 'TipoRelacion=04';
            $lines[] = 'UUID=[' . $relacionadoUuid . ']';
            $lines[] = '';
        }

        foreach ($conceptos as $c) {
            $objetoImp = $c['iva'] > 0 ? '02' : '01';
            $cBase     = number_format((float) $c['subtotal'], 2, '.', '');
            $cIva      = number_format((float) $c['iva'], 2, '.', '');
            $cValUnit  = number_format((float) $c['valor_unitario'], 2, '.', '');
            $cCant     = rtrim(rtrim(number_format((float) $c['cantidad'], 6, '.', ''), '0'), '.');

            $lines[] = '[Concepto|]';
            $lines[] = 'ClaveProdServ=' . $c['clave_prod_serv'];
            $lines[] = 'NoIdentificacion=';
            $lines[] = 'Cantidad=' . $cCant;
            $lines[] = 'ClaveUnidad=' . $c['clave_unidad'];
            $lines[] = 'ObjetoImp=' . $objetoImp;
            $lines[] = 'Unidad=PIEZA';
            $lines[] = 'Descripcion=' . $c['descripcion'];
            $lines[] = 'ValorUnitario=' . $cValUnit;
            $lines[] = 'Importe=' . $cBase;

            if ($c['iva'] > 0) {
                $lines[] = 'Impuestos.Traslados.Base=[' . $cBase . ']';
                $lines[] = 'Impuestos.Traslados.Impuesto=[002]';
                $lines[] = 'Impuestos.Traslados.TipoFactor=[Tasa]';
                $lines[] = 'Impuestos.Traslados.TasaOCuota=[0.160000]';
                $lines[] = 'Impuestos.Traslados.Importe=[' . $cIva . ']';
            }

            $lines[] = '';
        }

        if ((float) $totalIva > 0) {
            $bases    = [];
            $imptos   = [];
            $factores = [];
            $tasas    = [];
            $ivaTots  = [];

            foreach ($conceptos as $c) {
                if ($c['iva'] > 0) {
                    $bases[]    = '[' . number_format((float) $c['subtotal'], 2, '.', '') . ']';
                    $imptos[]   = '[002]';
                    $factores[] = '[Tasa]';
                    $tasas[]    = '[0.160000]';
                    $ivaTots[]  = '[' . number_format((float) $c['iva'], 2, '.', '') . ']';
                }
            }

            $lines[] = '[Traslados]';
            $lines[] = 'TotalImpuestosTrasladados=' . $totalIva;
            $lines[] = 'Base=' . implode('', $bases);
            $lines[] = 'Impuesto=' . implode('', $imptos);
            $lines[] = 'TasaOCuota=' . implode('', $tasas);
            $lines[] = 'TipoFactor=' . implode('', $factores);
            $lines[] = 'Importe=' . implode('', $ivaTots);
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Convierte error_code en mensaje amigable para el cajero. */
    private function mensajeAmigable(array $response): string
    {
        $code    = $response['error_code']    ?? '';
        $message = $response['error_message'] ?? ($response['mensaje'] ?? 'Error desconocido.');

        $amigables = [
            'CFDI40124' => 'El RFC del cliente no está registrado en el SAT. Pide que lo verifique.',
            'CFDI40125' => 'El RFC del cliente está inactivo ante el SAT.',
            'CFDI40126' => 'El nombre del cliente no coincide con el registro del SAT. Usa la razón social exacta.',
            'CFDI40127' => 'El régimen fiscal del cliente es incorrecto. Verifica con el cliente.',
            'CFDI40128' => 'El código postal del cliente no coincide con el SAT.',
            'CAF40001'  => 'La factura ya fue cancelada anteriormente.',
            'CAF40003'  => 'La factura no es cancelable (el receptor ya la rechazó).',
        ];

        return isset($amigables[$code])
            ? "[{$code}] {$amigables[$code]}"
            : ($code ? "[{$code}] {$message}" : $message);
    }

    /** Construye los conceptos desde las ventas seleccionadas. */
    private function buildConceptos($sales): array
    {
        $conceptos = [];

        foreach ($sales as $sale) {
            foreach ($sale->getDetails as $detail) {
                $presentation = PartToProduct::find($detail->part_to_product_id);
                $product      = $presentation ? Product::find($presentation->product_id) : null;

                $descripcion = $product?->description ?? 'Producto';
                $claveUnidad = $product?->unit ?? 'H87';
                $claveProd   = '01010101'; // sin clave SAT específica — actualizar si los productos tienen clave

                $subtotal = round((float) $detail->subtotal, 2);
                $iva      = round((float) $detail->iva, 2);

                $cantidad = max(1, (int) $detail->getCantSalesDetail->sum('cant'));

                $conceptos[] = [
                    'clave_prod_serv' => $claveProd,
                    'cantidad'        => $cantidad,
                    'clave_unidad'    => $claveUnidad,
                    'descripcion'     => preg_replace('/\s+/', ' ', trim($descripcion)),
                    'valor_unitario'  => round((float) $detail->unit_price, 2),
                    'subtotal'        => $subtotal,
                    'iva'             => $iva,
                    'total'           => round($subtotal + $iva, 2),
                ];
            }
        }

        return $conceptos;
    }
}
