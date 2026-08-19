<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmpresaDetail;
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Support\Facades\{Storage};
use iio\libmergepdf\Merger;
use Dompdf\Dompdf;

class ReportController extends Controller
{   
    //funcion para vista de reportes
    public function index(){
        return view('admin.reports.index');
    }

    public function pdf($startDate, $endDate, $code_product = null){
        $empresa = EmpresaDetail::first();
        $empresa->nombre = mb_convert_encoding($empresa->nombre, 'UTF-8', 'UTF-8');

        $logoPath = public_path('img/logo_cliente.png');
        $logoBase64 = $this->logoDataUri($logoPath);

        $dir = 'pdf_reports';
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        $products = $this->getReportData($empresa->id, $startDate, $endDate, $code_product);
        // $ultimo = collect($products)->last();
        $merger = new Merger();
        $products->chunk(200)->each(function ($chunk) use ($empresa, $logoBase64, $startDate, $endDate, $merger) {
            $pdf = Pdf::loadView('report_pdf', [
                'productos' => $chunk,
                'empresa'   => $empresa,
                'logoBase64'=> $logoBase64,
                'startDate' => $startDate,
                'endDate'   => $endDate
            ])->setPaper('a4', 'portrait');

            $merger->addRaw($pdf->output());
        });

        $finalPdf = $merger->merge();
        $filePath = storage_path("app/public/{$dir}/report.pdf");
        file_put_contents($filePath, $finalPdf);
        
        return response()->file($filePath);
    }

    public function getReportData($branch_id, $startDate, $endDate, $code_product)
    {
        $productsResponse = $this->productsData($code_product); 

        if (!isset($productsResponse->status) || $productsResponse->status !== 'success') {
            return collect();
        }

        $products = collect($productsResponse->data);

        $entradas = isset($this->entradasData($branch_id, $startDate, $endDate)->data) ? collect($this->entradasData($branch_id, $startDate, $endDate)->data) : collect();
        $ventas = isset($this->salesData($branch_id, $startDate, $endDate)->data) ? collect($this->salesData($branch_id, $startDate, $endDate)->data) : collect();

        return $products->map(function ($product) use ($entradas, $ventas) {
            $totalEntrada = 0;
            $totalSalida = 0;

            foreach ($entradas as $entrada) {
                $details = json_decode($entrada->compra_details_json);
                if ($details && $details->code_product === $product->code_product) {
                    $cantidad = isset($details->cant) 
                        ? $details->cant 
                        : ($details->subtotal / $details->precio_unitario); 
                    $totalEntrada += $cantidad;
                }
            }

            foreach ($ventas as $venta) {
                $details     = json_decode($venta->details_json);
                $detailCants = json_decode($venta->detail_cant_json);

                if (is_array($details) && is_array($detailCants)) {
                    foreach ($details as $d) {
                        // Match directo con el producto
                        if (isset($d->product_id) && (int)$d->product_id === (int)$product->id) {
                            $partId = $d->part_to_product_id ?? null;

                            // Buscar la cantidad correspondiente en detail_cant_json
                            foreach ($detailCants as $dc) {
                                if ($partId !== null && isset($dc->part_to_product_id) && $dc->part_to_product_id == $partId) {
                                    $totalSalida += (float)($dc->cant ?? 0);
                                }
                            }

                            // Como respaldo, calcular cantidad si no existe en cant_json
                            if (!isset($dc) || empty($dc->cant)) {
                                $cantidad = isset($d->cant)
                                    ? (float)$d->cant
                                    : ((isset($d->unit_price, $d->subtotal) && (float)$d->unit_price > 0)
                                        ? (float)$d->subtotal / (float)$d->unit_price
                                        : 0.0);

                                $totalSalida += $cantidad;
                            }
                        }
                    }
                }
            }

            $existenciaInicial = (float) $product->existence;
            $existenciaReal = $existenciaInicial + $totalEntrada - $totalSalida;
            $costoU = (float) $product->precio;
            $costoInventario = $existenciaReal * $costoU;

            return [
                'codigo' => $product->code_product,
                'descripcion' => $product->description,
                'linea' => $product->comments,
                'existencia_inicial' => $existenciaInicial,
                'total_entrada' => $totalEntrada,
                'total_salida' => $totalSalida,
                'existencia_real' => $existenciaReal,
                'costo_u' => $costoU,
                'costo_inventario' => $costoInventario,
            ];
        });
    }

    //funcion para obtener todos los productos
    private function productsData($code_product){
        if($this->hasInternetConnection()){
            if (!is_null($code_product)) {
                return $this->consultDb('products', ['code_product' => $code_product]);
            }else{
                return $this->consultDb('products', '');
            }
        }
        return null;
    }

    //funcion para obtener las compras ya recibidas por fecha 
    private function entradasData($branch_id, $startDate, $endDate){
        if($this->hasInternetConnection()){
            $data = [
                'branch_id' => $branch_id,
                'date' => [
                    'start' => $startDate,
                    'end' => $endDate, 
                ],
            ];
            return $this->consultDb('cuentas_pagar', $data);
        }
            return null;
    }

    //funcion para obtener las compras ya recibidas por fecha 
    private function salesData($branch_id, $startDate, $endDate){
        if($this->hasInternetConnection()){
            $data = [
                'branch_id' => $branch_id,
                'date' => [
                    'start' => $startDate,
                    'end' => $endDate, 
                    // 'end' => '2025-08-13', 
                ],
            ];
            return $this->consultDb('sales', $data);
        }
            return null;
    }
}

