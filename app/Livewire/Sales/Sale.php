<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Sale as SaleModel, Customer, PaymentMethod, Product, Price, SaleDetail, SaleDetailCant, PartToProduct, UnidadSat, Devolucion};
use Illuminate\Support\Facades\{Auth, Log};
use Livewire\Attributes\Validate;

class Sale extends Component
{
    protected $listeners = [
        'getDate' => 'getDate',
        'updateCant' => 'updateCant',
        'destroyProduct' => 'destroyProduct',
        'stockOff' => 'stockOff',
    ];

    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $type = '';

    //filtros
    public $paginate_cant = 25;
        public $search = '';
        public $filter = 0;

        public $customers = [];
        public $payment_methods = [];
        public $products = [];
        public $unidades_sat = [];
        public $prices = [];

        public $customer_id = '';
        public $date ='';
        public $whatDate = 'date';
        public $payment_method_id = '';
        public $coin = '';
        public $id = '';

        public $sales_detail = [];
        public $scan_presentation_id = '';
        public $total_sale = 0.00;
    public $total_desc = 0.00;

    public function mount($type, $id){
        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->payment_methods = PaymentMethod::orderBy('pay_method', 'asc')->orderBy('id','asc')->get();
        $this->date = [date('Y-m-d'), date('Y-m-d')];
        $this->type = $type;
        $this->products = Product::get();
        $this->id = $id;
    }

    public function render(){   
        $this->unidades_sat = UnidadSat::where('status', 1)->get();
        if($this->type == 'create'){//funcion para mostrar vista de crear
            return view('livewire.sales.create');
        }else if($this->type == 'show'){ //condicion para mostrar venta para editar
            $sale = SaleModel::find($this->id);
            $this->total_desc = SaleDetail::where('sale_id', $this->id)->sum('descuento');
            $this->total_sale = SaleDetail::where('sale_id', $this->id)->sum('total') - $this->total_desc;
            $this->sales_detail = SaleDetail::where('sale_id', $this->id)->get();
            // dd(count($this->sales_detail) && (int)$sale->amount_received === 0);
            $devoluciones = Devolucion::where('sale_id', $this->id)->get();
            return view('livewire.sales.show', ['sale' => $sale, 'devoluciones' => $devoluciones]);
        }

        if($this->search != ''){
            if(Auth::User()->hasRole('admin')){
                $sales = SaleModel::where('folio', 'LIKE', "%{$this->search}%")
                    ->orWhereHas('paymentMethod', function($query) {
                        $query->where('pay_method', 'LIKE', "%{$this->search}%");
                    })
                    ->orderBy($this->whatDate, 'desc')->paginate($this->paginate_cant);
            }else{
                $sales = SaleModel::where('user_id', Auth::User()->id)
                ->where('folio', 'LIKE', "%{$this->search}%")
                ->orWhereHas('paymentMethod', function($query) {
                    $query->where('pay_method', 'LIKE', "%{$this->search}%");
                })
                ->orderBy($this->whatDate, 'desc')->paginate($this->paginate_cant);
            }
        }else{
            if(Auth::User()->hasRole('admin')){
                $sales = SaleModel::whereBetween($this->whatDate, $this->date)
                    // ->where('folio', 'LIKE', "%{$this->search}%")
                    ->orderBy($this->whatDate, 'desc')->paginate($this->paginate_cant);
            }else{
                $sales = SaleModel::where('user_id', Auth::User()->id)
                        ->whereBetween($this->whatDate, $this->date)
                        // ->where('folio', 'LIKE', "%{$this->search}%")
                        ->orderBy($this->whatDate, 'desc')->paginate($this->paginate_cant);
            }
        }

        return view('livewire.sales.sale',['sales' => $sales]);
    }

    //funcion obtener fechas de filtro
    public function getDate($start, $end, $type){
        $start = $type != 'date' ? $start.' 00:00:00':$start;
        $end = $type != 'date' ? $end.' 23:59:59':$end;
        $this->date = [$start, $end];
        $this->whatDate = $type;
    }

    //funcion para ocultar o mostrar filtros
    public function showFilter(){
            $this->whatDate = 'date';
            $this->date = [date('Y-m-d'), date('Y-m-d')];
            $this->search = '';

            $this->dispatch('daterangepicker', $this->date);
    }

    //funcion para obtener datos con scaner
    public function scaner_codigo(){
        $presentation = PartToProduct::where('code_bar', $this->scan_presentation_id)->first();

        $this->scan_presentation_id = '';
        if(is_object($presentation)){
                $sale_detail = $this->saveSaleDetail($presentation); //guardamos en save_detail
                $sale_detail_cant = $this->saveSaleDetailCant($sale_detail, $presentation); //guardamos en save_detail_cant
                $response = $this->restarStock($presentation); //restamos el stock de la presentacion
                $this->newOrUpdateSaleDetailCant($response, $sale_detail, $sale_detail_cant, $presentation); //actualizamos o creamos nuevo registro sale_detail_cant
                $this->calculoImpuestos($sale_detail, $presentation); //calculo de impuestos
                $data = $this->getDataSales();

                $this->dispatch('scan', ['product' => $data['product_detail'], 'persentation' => $data['presentation_detail'], 
                                        'sales_detail' => $this->sales_detail, 'unidad_sat' => $data['unidad_sat'],
                                        'promotions' => $data['promotions'], 'cant_sales_detail' => $data['cant_sales_detail']]);
        }else{
            $this->dispatch('scan', ['error' => true]);
        }
    }

    //funcion para guardar en sale_detail
    function saveSaleDetail($presentation){
        $sale_details = SaleDetail::where('sale_id', $this->id)->where('part_to_product_id', $presentation->id)
                                    ->where('unit_price', $presentation->price)->get();
        if(count($sale_details)){
            $index = count($sale_details)-1; //obtenemos la ultima poscición del array
            $sale_detail = $sale_details[$index];
            $sale_detail = SaleDetail::find($sale_detail->id); //actualizamos el detalle de venta
        }else{
            $sale_detail = new SaleDetail(); //guardamos el detalle de venta
            $sale_detail->part_to_product_id = $presentation->id;
            $sale_detail->sale_id = $this->id;
            $sale_detail->unit_price = $presentation->price;
            $sale_detail->save();
        }

        return $sale_detail;
    }

    //funcion para guardar en sale_detail_cant
    function saveSaleDetailCant($sale_detail, $presentation){
        $sale_detail_cants = SaleDetailCant::where('sale_detail_id', $sale_detail->id)->where('part_to_product_id', $presentation->id)->get();

        if(!count($sale_detail_cants)){
            $sale_detail_cant = $sale_detail->saveNewCantDetails($sale_detail->id, $presentation, true);
        }

        return $sale_detail_cant ?? $sale_detail_cants;
    }

    //funcion para restar el stock y vigencia en presentacion
    function restarStock($presentation){
        $presentation->stock = $presentation->stock - 1;

        if($presentation->tipo_descuento != null && $presentation->vigencia_cantidad_fecha == 'cantidad'){
            $presentation->vigencia = (float)$presentation->vigencia - 1;
        }
        
        if($presentation->vigencia < 0){
            $presentation->vigencia = 0;
            $presentation->save();
            return 'new_or_update';
        }else{
            $presentation->save();
            return 'update';
        }
    }

    //funcion para actualizar o crear nuevo regstro en sale_detail_cant
    function newOrUpdateSaleDetailCant($response, $sale_detail, $sale_detail_cant, $presentation){
        if($response == 'new_or_update'){ //revisamos si ocupamos nuevo registro en sale_detail_cant por termino de vigencia de presentacion o solo actualizamos 
            $ban = false;
            if(is_countable($sale_detail_cant)){
                foreach($sale_detail_cant as $item){
                    if($item->part_to_product_id == $presentation->id && $item->descuento == 0 && $presentation->vigencia == 0){
                        $sale_detail_cant = $item;
                        $sale_detail_cant->cant = $sale_detail_cant->cant + 1;
                        $ban = true;
                        $sale_detail_cant->save();
                    }
                }
            }else{
                if($sale_detail_cant->part_to_product_id == $presentation->id && $sale_detail_cant->descuento == 0 && $presentation->vigencia == 0){
                    $sale_detail_cant->cant = $sale_detail_cant->cant + 1;
                    $ban = true;
                    $sale_detail_cant->save();
                }
            }

            if(!$ban){
                $sale_detail->saveNewCantDetails($sale_detail->id, $presentation, false);
            }
        }else{
            if(is_countable($sale_detail_cant)){
                foreach($sale_detail_cant as $item){
                    if($item->part_to_product_id == $presentation->id && $item->descuento == $presentation->monto_porcentaje){
                        $sale_detail_cant = $item;
                        $sale_detail_cant->cant = $sale_detail_cant->cant + 1;
                    }
                }
            }

            $sale_detail_cant->descuento = ($presentation->monto_porcentaje ?? 0);
            $sale_detail_cant->total_descuento = ($presentation->monto_porcentaje ?? 0) * $sale_detail_cant->cant;
            $sale_detail_cant->save();
        }
    }

     //funcion para el calculo de impuestos (iva, ieps, total)
    function calculoImpuestos($sale_detail, $presentation){
        $product = $presentation->getProducto($presentation->product_id);

        $cants = $sale_detail->getCantDetails($sale_detail->id, $presentation->id);

        $amount = $sale_detail->unit_price * $cants;
        $subtotal = $amount;
        $iva = $product->taxes == 'IVA' ? ($amount * $product->amount_taxes):0;
        $ieps = $product->taxes == 'IE3' ? ($amount * $product->amount_taxes):0;
        $total = ($subtotal + $iva + $ieps);

        $sale_detail->amount = $amount;
        $sale_detail->subtotal = $subtotal;
        $sale_detail->iva = $iva;
        $sale_detail->ieps = $ieps;
        $sale_detail->total = $total;
        $sale_detail->save();
    }

    //funcion para obtener datos para pintar en tabla
    function getDataSales(){
        $this->sales_detail = SaleDetail::where('sale_id', $this->id)->get();
        $this->total_sale = SaleDetail::where('sale_id', $this->id)->sum('total');

        $data = [];
        if(count($this->sales_detail)){ 
            foreach($this->sales_detail as $index => $item){
                $getPartToProductId = $item->getPartToProductId($item->part_to_product_id);
                
                $data['cant_sales_detail'][] = $item->getCantSalesDetail;
                $data['presentation_aux'] = $getPartToProductId->getPresentation->getUnidadSat;
                $data['promotions'] = $getPartToProductId->getPromotion;
                $data['presentation_detail'][] = $getPartToProductId;
                $data['product_detail'][] = $item->getProductId($getPartToProductId->product_id);
                $data['descuentos'][] = $item->descuentos;

                $this->total_sale -= $item->descuentos; 

                //unidades sat
                if(isset($data['presentation_aux']->clave_unidad) && isset($data['presentation_aux']->name)){ 
                    $data['unidad_sat'][$index] = $data['presentation_aux']->clave_unidad.' - '.$data['presentation_aux']->name;
                }
            }
        }

        return $data;
    } 

    


    //funcion para agregar manual la cantidad de productos
    public function updateCant($sale_detail_cant_id, $cant){  //seguirle aqui con las cantidades manuales
        $cant = (float)$cant;      
        $sale_detail_cant = SaleDetailCant::find($sale_detail_cant_id);
        $sale_detail = $sale_detail_cant->getSaleDetail;
        $presentation = $sale_detail->getPartToProductId($sale_detail->part_to_product_id);

        $cantidad = (float)$cant - 1;
        $desc_stock = false;
        
        if($sale_detail->nuevoReg($sale_detail_cant, $presentation)){
            $cantidad = (float)$cant;
        }

        dd($cantidad);

        if((float)$sale_detail_cant->cant > $cant){ //condicion para actualizar el stock del producto
            if(is_object($presentation)){
                $presentation_model = PartToProduct::find($presentation->id);
                
                if((float)$sale_detail_cant->descuento > 0){
                    $vigencia_actual = (float) $presentation_model->vigencia;
                    $vigencia = $vigencia_actual + ($sale_detail_cant->cant - $cant);
                }

                $presentation_model->stock = $presentation->stock + ($sale_detail_cant->cant - $cant);
                $presentation_model->vigencia = (string) $vigencia;
                $presentation_model->save();
                    
                $sale_detail_cant->cant = 0;
                $sale_detail_cant->save();

                $cantidad = $cant;
                $desc_stock = true;
            }
        } 

        if($cant > ($presentation->stock + $cantidad)){
                $this->dispatch('alert', ['message' => 'Stock insuficiente.']);
        }else{
            $cant_detail = $cant - $sale_detail_cant->cant;
            $product = $presentation->getProducto($presentation->product_id);
            for($i = 0; $i < $cant_detail; $i++){
                $presentation = $sale_detail->getPartToProductId($sale_detail->part_to_product_id);

                $this->saveDetail($presentation, $product, 'manual', $sale_detail_cant, $desc_stock);
                !$desc_stock ? $this->descStock($presentation):'';
                $data = $this->getDataSales();
                
                $this->dispatch('scan', ['product' => $data['product_detail'], 'persentation' => $data['presentation_detail'], 
                                    'sales_detail' => $this->sales_detail, 'unidad_sat' => $data['unidad_sat'],
                                    'promotions' => $data['promotions'], 'cant_sales_detail' => $data['cant_sales_detail']]);
            }
        }
    }

    //funcion para eliminar un producto ya agregado en la venta
    public function destroyProduct($sale_detail_cant_id){

        $sale_detail_cant = SaleDetailCant::find($sale_detail_cant_id);
        $sale_detail = $sale_detail_cant->getSaleDetail;
        $presentation = PartToProduct::find($sale_detail->part_to_product_id);
       
        $presentation->stock = $presentation->stock + $sale_detail_cant->cant;
        if($presentation->vigencia_cantidad_fecha == 'cantidad' && $sale_detail_cant->descuento > 0){
            $presentation->vigencia = $presentation->vigencia + $sale_detail_cant->cant;
        }

        $presentation->save();
        $sale_detail_cant->delete();

        if(!count($sale_detail->getCantSalesDetail)){
            $sale_detail_delete = SaleDetail::find($sale_detail->id)->delete();
        }else{
            $this->calculoImpuestos($sale_detail, $presentation);
        }     

        $data = $this->getDataSales();
        $this->dispatch('scan', ['product' => $data['product_detail'] ?? [], 'persentation' => $data['presentation_detail'] ?? [], 
                                'sales_detail' => $this->sales_detail, 'unidad_sat' => $data['unidad_sat'] ?? [],
                                'promotions' => $data['promotions'] ?? [], 'cant_sales_detail' => $data['cant_sales_detail'] ?? []]);
        
        // if(is_object($sale_detail) && is_object($sale_detail_cant)){
        //     $presentation = PartToProduct::find($sale_detail->part_to_product_id);

        //     if(is_object($presentation)){
        //             $presentation->stock = $presentation->stock + $sale_detail_cant->cant;
        //             if($presentation->vigencia_cantidad_fecha == 'cantidad' && (int)$sale_detail_cant->descuento > 0){
        //                 $presentation->vigencia = $presentation->vigencia + $sale_detail_cant->cant;
        //             }
        //             $presentation->save();
        //     }

        //     $sale_detail_cant->delete();
        //     $sale_detail->delete();

        // }
    }

    //funcion para agregar nota al detalle de venta sobre stock de presentacion
    function stockOff($sale_detail_id, $code){
        $sale_detail = SaleDetail::find($sale_detail_id);
        if(is_object($sale_detail)){
            $sale_detail->notes = $sale_detail->notes.' *Presentación de producto sin existencia: '.$code;
            $sale_detail->save();
        }
    }
}
