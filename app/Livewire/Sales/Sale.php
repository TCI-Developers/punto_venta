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
        'venta_mayoreo_save' => 'venta_mayoreo_save',
    ];

    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $type = '';
    public $data = [];

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
        public $sales_detail_dev = [];
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
            $this->sales_detail = SaleDetail::where('sale_id', $this->id)->get();
            $this->sales_detail_dev = SaleDetail::where('sale_id', $this->id)->where('status', 0)->get();
            $devoluciones = Devolucion::where('sale_id', $this->id)->get();
            return view('livewire.sales.show', ['sale' => $sale, 'devoluciones' => $devoluciones]);
        }
        $user = Auth::User();
        if($this->search != ''){
            if($user->hasRole(['root','admin'])){
                $sales = SaleModel::where('branch_id', $user->branch_id)->where('folio', 'LIKE', "%{$this->search}%")
                    ->orWhereHas('paymentMethod', function($query) {
                        $query->where('pay_method', 'LIKE', "%{$this->search}%");
                    })
                    ->orderBy($this->whatDate, 'desc')->paginate($this->paginate_cant);
            }else{
                $sales = SaleModel::where('user_id', $user->id)
                ->where('folio', 'LIKE', "%{$this->search}%")
                ->orWhereHas('paymentMethod', function($query) {
                    $query->where('pay_method', 'LIKE', "%{$this->search}%");
                })
                ->orderBy($this->whatDate, 'desc')->paginate($this->paginate_cant);
            }
        }else{
            if($user->hasRole(['root', 'admin'])){
                $sales = SaleModel::where('branch_id', $user->branch_id)->whereBetween($this->whatDate, $this->date)
                    // ->where('folio', 'LIKE', "%{$this->search}%")
                    ->orderBy($this->whatDate, 'desc')->paginate($this->paginate_cant);
            }else{
                $sales = SaleModel::where('user_id', $user->id)
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

            if($presentation->price_mayoreo > 0 && $presentation->cantidad_mayoreo > 0){
                $this->dispatch('venta_mayoreo', ['presentation' => $presentation]);
            }else{
                // $sale_detail = $this->saveSaleDetail($presentation); //guardamos en save_detail
                // $sale_detail_cant = $this->saveSaleDetailCant($sale_detail, $presentation); //guardamos en save_detail_cant
                // $response = $this->restarStock($presentation); //restamos el stock de la presentacion
                // $this->newOrUpdateSaleDetailCant($response, $sale_detail, $sale_detail_cant, $presentation); //actualizamos o creamos nuevo registro sale_detail_cant
                // $this->calculoImpuestos($sale_detail, $presentation); //calculo de impuestos
                // $data = $this->getDataSales();
                $this->data = $this->saveMenudeo($presentation);

                $this->dispatch('scan', ['product' => $this->data['product_detail'], 'persentation' => $this->data['presentation_detail'], 
                                        'sales_detail' => $this->sales_detail, 'unidad_sat' => $this->data['unidad_sat'],
                                        'promotions' => $this->data['promotions'], 'cant_sales_detail' => $this->data['cant_sales_detail']]);
            }           
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
            $sale_detail->unit_price = round($presentation->price, 2);
            $sale_detail->save();
        }

        return $sale_detail;
    }

    //funcion para guardar en sale_detail_cant
    function saveSaleDetailCant($sale_detail, $presentation){
        $sale_detail_cants = SaleDetailCant::where('sale_id', $this->id)->where('sale_detail_id', $sale_detail->id)->where('part_to_product_id', $presentation->id)->get();

        if(!count($sale_detail_cants)){
            $withDesc = false;
           
            if(!is_null($presentation->vigencia) && !is_null($presentation->tipo_descuento) && $presentation->vigencia_cantidad_fecha == 'cantidad' && $presentation->vigencia > 0){
                $withDesc = true;
            }else if(!is_null($presentation->vigencia) && !is_null($presentation->tipo_descuento) && $presentation->vigencia_cantidad_fecha == 'fecha' && $presentation->vigencia < date('Y-m-d H:i:s')){
                $withDesc = true;
            }

            $sale_detail_cant = $sale_detail->saveNewCantDetails($sale_detail, $presentation, $withDesc);
        }

        return $sale_detail_cant ?? $sale_detail_cants;
    }

    //funcion para restar el stock y vigencia en presentacion
    function restarStock($presentation){
        $this->setStock($presentation, null, 'menos');

        if(!is_null($presentation->vigencia) && !is_null($presentation->tipo_descuento) && $presentation->vigencia_cantidad_fecha == 'cantidad'){
                $presentation->vigencia = (float)$presentation->vigencia - 1;
        }
        
        if(!is_null($presentation->vigencia) && $presentation->vigencia < 0){
            $presentation->vigencia = null;
            $presentation->save();
            return 'new_or_update';
        }else{
            $presentation->save();
            return 'update';
        }
    }

    //funcion para sumar vigencia en presentacion
    function sumarVigencia($presentation, $sale_detail_cant){

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
                $sale_detail->saveNewCantDetails($sale_detail, $presentation, false);
            }
        }else{
            if(is_countable($sale_detail_cant)){
                foreach($sale_detail_cant as $item){
                    if($item->part_to_product_id == $presentation->id){
                        if(!is_null($presentation->vigencia) && $item->descuento == $presentation->monto_porcentaje || is_null($presentation->vigencia) && $item->descuento == 0){
                            $sale_detail_cant = $item;
                            $sale_detail_cant->cant = $sale_detail_cant->cant + 1;
                        }
                    }
                }
            }

            $sale_detail_cant->descuento = 0;
            if(!is_null($presentation->vigencia) && $presentation->tipo_descuento == 'monto'){
                $sale_detail_cant->descuento = round(($presentation->monto_porcentaje ?? 0), 2);
                $sale_detail_cant->total_descuento = round(((($presentation->monto_porcentaje ?? 0) * $sale_detail_cant->cant)), 2);
            }else if(!is_null($presentation->vigencia) && $presentation->tipo_descuento == 'porcentaje'){
                $sale_detail_cant->descuento = round(($presentation->monto_porcentaje ?? 0),2);
                $sale_detail_cant->total_descuento = 0;
                if(!is_null($presentation->monto_porcentaje) && $presentation->monto_porcentaje > 0){
                    $sale_detail_cant->total_descuento = round(((($presentation->price * $presentation->monto_porcentaje)/100)*$sale_detail_cant->cant),2);
                }
            }

            $sale_detail_cant->save();
        }
    }

     //funcion para el calculo de impuestos (iva, ieps, total)
    function calculoImpuestos($sale_detail, $presentation){
        $product = $presentation->getProducto($presentation->product_id);

        $cants = $sale_detail->getCantDetails($sale_detail->id, $presentation->id);

        $amount = $sale_detail->unit_price * $cants;
        $subtotal = $amount;
        $iva = round(($product->taxes == 'IVA' ? ($amount * $product->amount_taxes):0),2);
        $ieps = round(($product->taxes == 'IE3' ? ($amount * $product->amount_taxes):0),2);
        $total = round(($subtotal + $iva + $ieps), 2);

        $sale_detail->amount = round($amount);
        $sale_detail->subtotal = round($subtotal);
        $sale_detail->iva = round($iva);
        $sale_detail->ieps = round($ieps);
        $sale_detail->total = round($total);
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
                $data['presentation_aux'] = $getPartToProductId->getProduct;
                $data['promotions'] = $getPartToProductId->getPromotion;
                $data['presentation_detail'][] = $getPartToProductId;
                $data['product_detail'][] = $item->getProductId($getPartToProductId->product_id);
                $data['descuentos'][] = $item->descuentos;

                $this->total_sale -= $item->descuentos; 

                //unidades sat
                if(isset($data['presentation_aux']->unit) && isset($data['presentation_aux']->unit_description)){ 
                    $data['unidad_sat'][$index] = $data['presentation_aux']->unit.' - '.$data['presentation_aux']->unit_description;
                }
            }
        }

        return $data;
    } 

    //funcion para guardar producto individual
    function saveMenudeo($presentation){
        $sale_detail = $this->saveSaleDetail($presentation); //guardamos en save_detail
        $sale_detail_cant = $this->saveSaleDetailCant($sale_detail, $presentation); //guardamos en save_detail_cant
        $response = $this->restarStock($presentation); //restamos el stock de la presentacion
        $this->newOrUpdateSaleDetailCant($response, $sale_detail, $sale_detail_cant, $presentation); //actualizamos o creamos nuevo registro sale_detail_cant
        $this->calculoImpuestos($sale_detail, $presentation); //calculo de impuestos
        $data = $this->getDataSales();

        return $data;
    }

    //funcion para agregar producto con precio de mayoreo
    function venta_mayoreo_save($presentation, $status, $cant){
        if($status){
            $exist_sale_detail_cant = SaleDetailCant::where('part_to_product_id', $presentation['id'])->first();
            if(is_object($exist_sale_detail_cant)){
                $this->dispatch('alert', ['message' => 'Este producto ya esta registrado, para agregarlo como mayoreo, eliminalo primero.']);
            }
            // $sale_detail = $this->saveSaleDetail($presentation); SEGUIR AQUI CON LOS DE MAYOREO
        }else{
            $this->saveMenudeo($presentation);
        }
    }
// *****************************************************************
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
    },,

    //funcion para eliminar un producto ya agregado en la venta
    public function destroyProduct($sale_detail_cant_id){

        $sale_detail_cant = SaleDetailCant::find($sale_detail_cant_id);
        $sale_detail = $sale_detail_cant->getSaleDetail;
        $presentation = PartToProduct::find($sale_detail->part_to_product_id);
        
        $this->setStock($presentation, $sale_detail_cant->cant, 'mas');

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

    }

    // funcion para cambiar el stock del producto
    function setStock($presentation, $cant, $type = 'menos'){
        $presentacionese_existentes = PartToProduct::where('product_id', $presentation->product_id)->get();
        if(count($presentacionese_existentes)){
            foreach($presentacionese_existentes as $item){
                if($type == 'mas'){
                    $item->stock = $item->stock + $cant;
                }else{
                    $item->stock = $item->stock - 1;
                }
                $item->save();
            }
        }
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
