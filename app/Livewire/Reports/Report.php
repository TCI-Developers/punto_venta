<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Http\Controllers\Controller;
use App\Models\EmpresaDetail;

class Report extends Component
{   

    public $page = 1;         // Página actual
    public $perPage = 50;     // Registros por página
    public $totalPages = 1; 
    public $reportData = []; 

    public $branch_id = '';
    public $search = '';
    public $startDate = '';
    public $endDate = '';

    public $filtrado = false;

    function mount(){
        $this->setDates();
        $this->branch_id = EmpresaDetail::pluck('id')->first();

        // Cargar totalPages desde el principio
        $productsResponse = $this->getProducts();
        if ($productsResponse && isset($productsResponse->total_pages)) {
            $this->totalPages = $productsResponse->total_pages;
        }
        $this->reportData = $this->getReportData();
    }

    public function render()
    {     
        return view('livewire.reports.report');
    }

    public function getReportData()
    {
        $productsResponse = $this->getProducts();

        if (!isset($productsResponse->status) || $productsResponse->status !== 'success') {
            return collect();
        }

        $products = collect($productsResponse->data);
        $this->totalPages = $productsResponse->total_pages ?? 1;

        $entradas = isset($this->getEntradas()->data) ? collect($this->getEntradas()->data) : collect();
        $ventas = isset($this->getSales()->data) ? collect($this->getSales()->data) : collect();

        return $products->map(function ($product) use ($entradas, $ventas) {
            $totalEntrada = 0;
            $totalSalida = 0;

            foreach ($entradas as $entrada) {
                $details = json_decode($entrada->compra_details_json);
                if ($details && $details->code_product === $product->code_product) {
                    $cantidad = isset($details->cant) 
                        ? $details->cant 
                        : ($details->subtotal / $details->precio_unitario); // cálculo alterno
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
    function getProducts(){
        $ctrl = new Controller();
        if($ctrl->hasInternetConnection()){
            if (!empty($this->search)) {
                return $ctrl->consultDb('products', ['code_product' => $this->search]);
            }else{
                return $ctrl->consultDbPaginate('products', $this->page, $this->perPage);
            }
        }
            return null;
    }

    //funcion para obtener las compras ya recibidas por fecha 
    function getEntradas(){
        $ctrl = new Controller();
        if($ctrl->hasInternetConnection()){
            $data = [
                'branch_id' => $this->branch_id,
                'date' => [
                    'start' => $this->startDate,
                    'end' => $this->endDate, 
                ],
            ];
            return $ctrl->consultDb('cuentas_pagar', $data);
        }
            return null;
    }

    //funcion para obtener las compras ya recibidas por fecha 
    function getSales(){
        $ctrl = new Controller();
        if($ctrl->hasInternetConnection()){
            $data = [
                'branch_id' => $this->branch_id,
                'date' => [
                    'start' => $this->startDate,
                    'end' => $this->endDate, 
                    // 'end' => '2025-08-13', 
                ],
            ];
            return $ctrl->consultDb('sales', $data);
        }
            return null;
    }

    public function nextPage()
    {
        if ($this->page < $this->totalPages) {
            $this->page++;
        }
    }

    public function prevPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function goToPage($pageNumber)
    {
        if ($pageNumber >= 1 && $pageNumber <= $this->totalPages) {
            $this->page = $pageNumber;
        }
    }

    //funcion para filtrar
    public function filtrar($type){
        if($type){
            // $this->reportData = [];
            $this->reportData = $this->getReportData();
        }else{
            $this->setDates();
            $this->search = '';
            $this->reportData = $this->getReportData();
        }
    }

    //funcion para buscar por codigo de product0
    public function searchProduct(){
        if (!empty($this->search)) {
            $this->reportData = $this->getReportData();
        }
    }

    private function setDates(){
        $this->startDate = date('Y-m-d', strtotime('-7 days'));
        $this->endDate = date('Y-m-d');
        // $this->endDate = '2025-08-20';
    }
}
