<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacturacionService
{
    private string $baseUrl;
    private bool $demo;

    public function __construct()
    {
        $this->baseUrl = config('facturacion.url', 'http://localhost:9000');
        $this->demo    = config('facturacion.demo', true);
    }

    /**
     * Timbra un CFDI 4.0 ante el SAT vía Dinvbox.
     *
     * @param  string  $xmlLayout  XML del CFDI 4.0 sin timbrar (se codifica en base64 aquí)
     * @param  array   $metadata   ['id'=>int, 'folio'=>string, 'tipo'=>string, 'concepto'=>string, 'relacionUUID'=>string]
     * @return array   ['success'=>bool, 'uuid'=>string, 'xml_url'=>string, 'pdf_url'=>string, 'xml'=>string, 'pdf'=>string, 'error_code'=>string, 'error_message'=>string]
     */
    public function timbrar(string $xmlLayout, array $metadata): array
    {
        try {
            $response = Http::timeout(60)->post("{$this->baseUrl}/api/timbrado", [
                'txt'          => base64_encode($xmlLayout),
                'idFactura'    => $metadata['folio'] ?? ('FAC-' . $metadata['id']),
                'record'       => $metadata['id'],
                'tipo'         => $metadata['tipo'] ?? 'I',
                'concepto'     => $metadata['concepto'] ?? 'Venta en punto de venta',
                'host'         => '',
                'idTablaQB'    => '',
                'idTablaQBLog' => '',
                'clist'        => '',
                'relacionUUID' => $metadata['relacionUUID'] ?? '',
            ]);

            return $response->json() ?? ['success' => false, 'error_message' => 'Sin respuesta del servicio de facturación.'];
        } catch (\Throwable $e) {
            return [
                'success'       => false,
                'error_message' => 'No se pudo conectar al servicio de facturación: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cancela un CFDI vigente.
     *
     * @param  string  $uuid       UUID del CFDI a cancelar
     * @param  string  $motivo     '01'|'02'|'03'|'04'
     * @param  string  $folioSust  UUID de la factura sustituta (solo si motivo='01')
     * @return array   ['success'=>bool, 'uuid'=>string, 'mensaje'=>string, 'error_code'=>string, 'error_message'=>string]
     */
    public function cancelar(string $uuid, string $motivo, string $folioSust = ''): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/api/cancelaciones", [
                'uuid'      => $uuid,
                'motivo'    => $motivo,
                'foliosust' => $folioSust,
                'demo'      => $this->demo ? 'demo' : '',
                'record'    => 0,
                'campo'     => 0,
                'bd'        => '',
            ]);

            return $response->json() ?? ['success' => false, 'error_message' => 'Sin respuesta del servicio de facturación.'];
        } catch (\Throwable $e) {
            return [
                'success'       => false,
                'error_message' => 'No se pudo conectar al servicio de facturación: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Consulta el estado vigente de un CFDI en el SAT.
     *
     * @return array ['success'=>bool, 'estado'=>string, 'esCancelable'=>string, 'estatusCancelacion'=>string]
     */
    public function consultarEstado(string $uuid, string $rfcReceptor, string $total): array
    {
        try {
            $response = Http::timeout(20)->post("{$this->baseUrl}/api/consulta", [
                'uuid'        => $uuid,
                'rfcReceptor' => $rfcReceptor,
                'total'       => $total,
                'demo'        => $this->demo ? 'demo' : '',
            ]);

            return $response->json() ?? ['success' => false, 'error_message' => 'Sin respuesta del servicio.'];
        } catch (\Throwable $e) {
            return [
                'success'       => false,
                'error_message' => 'No se pudo conectar al servicio de facturación: ' . $e->getMessage(),
            ];
        }
    }
}
