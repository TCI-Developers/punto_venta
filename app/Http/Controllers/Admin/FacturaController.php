<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Factura, Sale, SaleDetail, Customer, EmpresaDetail, PartToProduct, Product};

class FacturaController extends Controller
{
    const RFC_PUBLICO_GENERAL    = 'XAXX010101000';
    const NOMBRE_PUBLICO_GENERAL = 'PUBLICO EN GENERAL';
    const REGIMEN_PUBLICO_GENERAL = '616';
    const USO_CFDI_PUBLICO_GENERAL = 'S01';
    const CP_PUBLICO_GENERAL     = '99999';

    // Listado de facturas
    public function index()
    {
        $facturas = Factura::with(['customer', 'user'])->orderBy('id', 'desc')->get();
        return view('Admin.facturas.index', ['facturas' => $facturas]);
    }

    // Vista para crear factura (opcionalmente pre-selecciona una venta)
    public function create($sale_id = null)
    {
        $empresa   = EmpresaDetail::first();
        $customers = Customer::where('status', 1)->orderBy('name')->get();

        // Ventas cerradas que no tienen factura timbrada
        $ventas = Sale::where('status', 2)
            ->whereDoesntHave('facturas', fn ($q) => $q->where('status', 1))
            ->orderBy('id', 'desc')
            ->get();

        return view('Admin.facturas.create', [
            'empresa'   => $empresa,
            'customers' => $customers,
            'ventas'    => $ventas,
            'sale_id'   => $sale_id,
        ]);
    }

    // Guardar y timbrar factura
    public function store(Request $request)
    {
        $request->validate([
            'sale_ids'    => 'required|array|min:1',
            'sale_ids.*'  => 'required|integer|exists:sales,id',
            'uso_cfdi'    => 'required|string|max:3',
            'metodo_pago' => 'required|string|max:3',
            'forma_pago'  => 'required|string|max:3',
        ], [
            'sale_ids.required' => 'Selecciona al menos una venta.',
            'sale_ids.min'      => 'Selecciona al menos una venta.',
            'uso_cfdi.required' => 'El uso de CFDI es requerido.',
            'metodo_pago.required' => 'El método de pago es requerido.',
            'forma_pago.required'  => 'La forma de pago es requerida.',
        ]);

        $empresa  = EmpresaDetail::first();
        $user     = Auth::user();
        $publico  = $request->boolean('publico_general');

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

        // Obtener ventas válidas
        $sales = Sale::whereIn('id', $request->sale_ids)->where('status', 2)->get();
        if ($sales->count() !== count($request->sale_ids)) {
            return redirect()->back()->withInput()->with('error',
                'Una o más ventas seleccionadas no son válidas o ya fueron facturadas.');
        }

        // Construir conceptos y calcular totales
        $conceptos = $this->buildConceptos($sales);
        $subtotal  = round(collect($conceptos)->sum('subtotal'), 2);
        $iva       = round(collect($conceptos)->sum('iva'), 2);
        $total     = round($subtotal + $iva, 2);

        // Payload para el servicio de facturación
        $payload = [
            'emisor' => [
                'rfc'            => $empresa->rfc,
                'nombre'         => $empresa->razon_social ?? $empresa->name,
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

        // Crear registro de factura en status 0 (pendiente)
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
        $factura->save();

        // Asociar ventas al pivot
        $factura->sales()->attach($request->sale_ids);

        // Enviar al servicio de facturación
        $response = $this->sendToService($payload);

        if ($response['success']) {
            $factura->status        = 1;
            $factura->uuid          = $response['uuid']         ?? null;
            $factura->folio_fiscal  = $response['folio_fiscal'] ?? null;
            $factura->serie         = $response['serie']        ?? null;
            $factura->folio         = $response['folio']        ?? null;
            $factura->xml           = $response['xml']          ?? null;
            $factura->pdf_url       = $response['pdf_url']      ?? null;
            $factura->response_json = json_encode($response);
            $factura->save();

            return redirect()->route('facturas.show', $factura->id)
                ->with('success', 'Factura timbrada con éxito. UUID: ' . ($factura->uuid ?? $factura->id));
        } else {
            $factura->status        = 3;
            $factura->error_message = $response['message'] ?? 'Error desconocido del servicio de facturación.';
            $factura->response_json = json_encode($response);
            $factura->save();

            return redirect()->route('facturas.show', $factura->id)
                ->with('error', 'Error al timbrar: ' . $factura->error_message);
        }
    }

    // Detalle de factura
    public function show($id)
    {
        $factura = Factura::with(['customer', 'user', 'sales.getDetails'])->find($id);
        if (!is_object($factura)) {
            return redirect()->route('facturas.index')->with('error', 'Factura no encontrada.');
        }
        return view('Admin.facturas.show', ['factura' => $factura]);
    }

    // Cancelar factura
    public function cancel($id)
    {
        $factura = Factura::find($id);
        if (!is_object($factura)) {
            return redirect()->back()->with('error', 'Factura no encontrada.');
        }
        if ((int) $factura->status !== 1) {
            return redirect()->back()->with('error', 'Solo se pueden cancelar facturas timbradas.');
        }

        // TODO: llamar al servicio de cancelación antes de marcar como cancelada
        $factura->status = 2;
        $factura->save();

        return redirect()->back()->with('success', 'Factura cancelada.');
    }

    // ─── Servicio externo ──────────────────────────────────────────────────────

    /**
     * Envía el CFDI al servicio de facturación externo.
     *
     * El payload contiene: emisor, receptor, comprobante, conceptos.
     * Debe retornar:
     *   ['success' => true,  'uuid' => '...', 'xml' => '...', 'pdf_url' => '...']
     *   ['success' => false, 'message' => 'Descripción del error']
     */
    private function sendToService(array $payload): array
    {
        // TODO: reemplazar con la llamada real al servicio de facturación.
        //
        // Ejemplo con el cliente HTTP de Laravel:
        //
        // $response = \Illuminate\Support\Facades\Http::withToken(env('FACTURACION_API_KEY'))
        //     ->post(env('FACTURACION_URL') . '/cfdi', $payload);
        //
        // if ($response->successful()) {
        //     return [
        //         'success'      => true,
        //         'uuid'         => $response->json('uuid'),
        //         'folio_fiscal' => $response->json('folio_fiscal'),
        //         'serie'        => $response->json('serie'),
        //         'folio'        => $response->json('folio'),
        //         'xml'          => $response->json('xml'),
        //         'pdf_url'      => $response->json('pdf_url'),
        //     ];
        // }
        //
        // return [
        //     'success' => false,
        //     'message' => $response->json('message') ?? $response->body(),
        // ];

        // ── STUB: remover cuando el servicio esté configurado ──────────────
        return [
            'success' => false,
            'message' => 'Servicio de facturación no configurado. Implementa sendToService() en FacturaController.',
        ];
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function buildConceptos($sales): array
    {
        $conceptos = [];

        foreach ($sales as $sale) {
            foreach ($sale->getDetails as $detail) {
                $presentation = PartToProduct::find($detail->part_to_product_id);
                $product      = $presentation ? Product::find($presentation->product_id) : null;

                $descripcion = $product?->description ?? 'Producto';
                $unidad      = $product?->unit         ?? 'H87';   // H87 = pieza (SAT)
                $clave_prod  = '01010101';                          // sin clave SAT específica

                $subtotal = round((float) $detail->subtotal, 2);
                $iva      = round((float) $detail->iva, 2);

                $conceptos[] = [
                    'clave_prod_serv' => $clave_prod,
                    'cantidad'        => (float) $detail->cant,
                    'clave_unidad'    => $unidad,
                    'descripcion'     => $descripcion,
                    'valor_unitario'  => round((float) $detail->unit_price, 2),
                    'subtotal'        => $subtotal,
                    'iva'             => $iva,
                    'total'           => round($subtotal + $iva, 2),
                    'folio_venta'     => $sale->folio,
                ];
            }
        }

        return $conceptos;
    }
}
