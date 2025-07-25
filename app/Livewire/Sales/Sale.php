<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Sale as SaleModel, Customer, PaymentMethod, Product, EmpresaDetail, SaleDetail, SaleDetailCant, PartToProduct, UnidadSat, Devolucion};
use Illuminate\Support\Facades\{Auth};

class Sale extends Component
{
    protected $listeners = [
        'getDate' => 'getDate',
        'updateCant' => 'updateCant',
        'destroyProduct' => 'destroyProduct',
        'stockOff' => 'stockOff',
        'cobrar' => 'cobrar',
    ];

    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $type = '';
    protected $queryString = ['type'];
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
            return view('livewire.sales.show', ['sale' => $sale, 'devoluciones' => $devoluciones, 'products_' => Product::paginate(25)]);
        }
        $user = Auth::User();
        $empresa = EmpresaDetail::first();
        $this->products = Product::get();

        if($this->search != ''){
            if($user->hasRole(['root','admin'])){
                $sales = SaleModel::where('status', '!=', 0)->where('branch_id', $empresa->branch_id)
                   ->where(function($query) {
                    $query->where('folio', 'LIKE', "%{$this->search}%")
                        ->orWhere('date', 'LIKE', date('Y-m-d', strtotime($this->search)))
                        ->orWhereHas('paymentMethod', function($q) {
                            $q->where('pay_method', 'LIKE', "%{$this->search}%");
                        });
                    })
                    ->orderBy('folio', 'desc')->paginate($this->paginate_cant);
            }else{
                $sales = SaleModel::where('status', '!=', 0)->where('user_id', $user->id)
                    ->where(function($query) {
                    $query->where('folio', 'LIKE', "%{$this->search}%")
                        ->orWhere('date', 'LIKE', date('Y-m-d', strtotime($this->search)))
                        ->orWhereHas('paymentMethod', function($q) {
                            $q->where('pay_method', 'LIKE', "%{$this->search}%");
                        });
                    })
                ->orderBy('id', 'desc')->paginate($this->paginate_cant);
            }
        }else{
            if($user->hasRole(['root', 'admin'])){
                $sales = SaleModel::where('status', '!=', 0)->where('branch_id', $empresa->branch_id)->whereBetween($this->whatDate, $this->date)
                    ->orderBy('id', 'desc')->paginate($this->paginate_cant);
            }else{
                $sales = SaleModel::where('status', '!=', 0)->where('user_id', $user->id)
                        ->whereBetween($this->whatDate, $this->date)
                        ->orderBy('id', 'desc')->paginate($this->paginate_cant);
            }
        }

        $total_efectivo = 0;
        $total_tarjeta = 0;
        foreach($sales as $item){
            $total_efectivo += $item->type_payment == 'efectivo' ? $item->total_sale:0;
            $total_tarjeta += $item->type_payment == 'tarjeta' ? $item->total_sale:0;
        }

        return view('livewire.sales.sale',['sales' => $sales, 'total_efectivo' => $total_efectivo, 'total_tarjeta' => $total_tarjeta]);
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
    public function scaner_codigo($code_bar = null){
        $code = $code_bar ?? $this->scan_presentation_id;
        $presentation = PartToProduct::where('code_bar', $code)->first();
        // $presentation = PartToProduct::where('code_bar', $code)->where('stock', '>', 0)->first(); //old

        $this->scan_presentation_id = '';
        if(is_object($presentation)){
            $cantidad = $this->mayoreo_or_menudeo($presentation);
            if($cantidad){
                $this->venta_mayoreo_save($presentation, $cantidad);
            }else{
                $this->saveMenudeo($presentation);
            }   
        }
    }

    //funcion para guardar en sale_detail
    function saveSaleDetail($presentation, $type = null){
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
            $sale_detail->unit_price = round((is_null($type) ? $presentation->price:$presentation->price_mayoreo), 2);
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
            }else if(!is_null($presentation->vigencia) && !is_null($presentation->tipo_descuento) && $presentation->vigencia_cantidad_fecha == 'fecha' && $presentation->vigencia.' 23:59:59' > date('Y-m-d H:i:s')){
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
                        $sale_detail_cant = SaleDetailCant::find($item->id);

                        if(!is_null($presentation->vigencia)){
                            if(is_numeric($presentation->vigencia)){
                                if($item->descuento == $presentation->monto_porcentaje || is_null($presentation->vigencia) && $item->descuento == 0){
                                    $sale_detail_cant->cant = $sale_detail_cant->cant + 1;
                                }
                            }else{
                                if($presentation->vigencia.' 23:59:59' > date('Y-m-d H:i:s') && $item->descuento > 0 ||
                                    $presentation->vigencia.' 23:59:59' < date('Y-m-d H:i:s') && $item->descuento == 0){
                                    $sale_detail_cant->cant = $sale_detail_cant->cant + 1;
                                }
                            }
                        }else{
                            $sale_detail_cant->cant = $sale_detail_cant->cant + 1;
                        }
                    }
                }
            }

            $sale_detail_cant->descuento = 0;
            if(!is_null($presentation->vigencia) && $presentation->tipo_descuento == 'monto'){
                if(is_numeric($presentation->vigencia) || $presentation->vigencia.' 23:59:59' > date('Y-m-d H:i:s')){
                    $sale_detail_cant->descuento = round(($presentation->monto_porcentaje ?? 0), 2);
                    $sale_detail_cant->total_descuento = round(((($presentation->monto_porcentaje ?? 0) * $sale_detail_cant->cant)), 2);
                }
            }else if(!is_null($presentation->vigencia) && $presentation->tipo_descuento == 'porcentaje'){
                if(is_numeric($presentation->vigencia) || $presentation->vigencia.' 23:59:59' > date('Y-m-d H:i:s')){
                    $sale_detail_cant->descuento = round(($presentation->monto_porcentaje ?? 0),2);
                    $sale_detail_cant->total_descuento = 0;
                    if(!is_null($presentation->monto_porcentaje) && $presentation->monto_porcentaje > 0){
                        $sale_detail_cant->total_descuento = round(((($presentation->price * $presentation->monto_porcentaje)/100)*$sale_detail_cant->cant),2);
                    }
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

        $sale_detail->amount = round($amount,2);
        $sale_detail->subtotal = round($subtotal,2);
        $sale_detail->iva = round($iva,2);
        $sale_detail->ieps = round($ieps,2);
        $sale_detail->total = round($total,2);
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
        $this->data = $this->getDataSales();

        $this->dispatch('scan', ['product' => $this->data['product_detail'], 'persentation' => $this->data['presentation_detail'], 
                                        'sales_detail' => $this->sales_detail, 'unidad_sat' => $this->data['unidad_sat'],
                                        'promotions' => $this->data['promotions'], 'cant_sales_detail' => $this->data['cant_sales_detail']]);
    }

    //funcion para saber si es mayoreo o menudeo
    function mayoreo_or_menudeo($presentation, $total_cant = 0, $manual = null){
        $total_cant = SaleDetailCant::where('sale_id', $this->id)
        ->where('part_to_product_id', $presentation->id)
        ->sum('cant');

        $total_cant += 1;

        if ($presentation->cantidad_mayoreo > 0 
            && $presentation->price_mayoreo > 0 
            && $presentation->cantidad_mayoreo <= $total_cant) {

            SaleDetailCant::where('sale_id', $this->id)
                ->where('part_to_product_id', $presentation->id)
                ->delete();

            return $total_cant;
        }

        return false;

        // $cantidad_sales_detail_cant = SaleDetailCant::where('sale_id', $this->id)->where('part_to_product_id', $presentation->id)->get(); //cantidad del producto escaneado
            // if(count($cantidad_sales_detail_cant)){
            //     $total_cant = $cantidad_sales_detail_cant->sum('cant')+1; //total cantidad de producto escaneado mas 1 para tomar en cuenta el escaneado
            //     $multiple_data = count($cantidad_sales_detail_cant) == $total_cant ? true:false; //variable para saber si existen varios registros con el mismo producto
                
            //     if($presentation->cantidad_mayoreo > 0 && $presentation->price_mayoreo > 0 && $presentation->cantidad_mayoreo <= $total_cant){               
            //         foreach($cantidad_sales_detail_cant as $item){
            //             $this->destroyProduct($item->id, 'mayoreo');
            //         }
            //         return $total_cant;
            //     }
            // }
        // return false;
    }

    //funcion para agregar producto con precio de mayoreo
    function venta_mayoreo_save($presentation, $cant = 0){
        
            $exist_sale_detail_cant = SaleDetailCant::where('sale_id', $this->id)->where('part_to_product_id', $presentation['id'])->first();

            if(is_object($exist_sale_detail_cant)){
                $this->dispatch('alert', ['message' => 'Este producto ya esta registrado, para agregarlo como mayoreo, eliminalo primero.']);
            }else{   
                $presentation = PartToProduct::find($presentation['id']);
                $sale_detail = $this->saveSaleDetail($presentation, 'mayoreo');
                $sale_detail_cant = $sale_detail->saveNewCantDetails($sale_detail, $presentation, false, $cant);
                
                $this->setStock($presentation, $cant, 'mayoreo');
                $this->calculoImpuestos($sale_detail, $presentation); //calculo de impuestos

                $this->data = $this->getDataSales();

                $this->dispatch('scan', ['product' => $this->data['product_detail'], 'persentation' => $this->data['presentation_detail'], 
                                        'sales_detail' => $this->sales_detail, 'unidad_sat' => $this->data['unidad_sat'],
                                        'promotions' => $this->data['promotions'], 'cant_sales_detail' => $this->data['cant_sales_detail']]);
            }
    }    

    //funcion para eliminar un producto ya agregado en la venta
    public function destroyProduct($sale_detail_cant_id, $status = null){
        $sale_detail_cant = SaleDetailCant::find($sale_detail_cant_id);
        $sale_detail = $sale_detail_cant->getSaleDetail;
        $presentation = PartToProduct::find($sale_detail->part_to_product_id);
        
        $this->setStock($presentation, $sale_detail_cant->cant, 'mas');

        if($presentation->vigencia_cantidad_fecha == 'cantidad' && $sale_detail_cant->descuento > 0){
            $presentation->vigencia = (float)$presentation->vigencia + (float)$sale_detail_cant->cant;
        }

        $presentation->save();
        $sale_detail_cant->delete();

        if(!count($sale_detail->getCantSalesDetail)){
            $sale_detail_delete = SaleDetail::find($sale_detail->id)->delete();
        }else{
            $this->calculoImpuestos($sale_detail, $presentation);
        }     

        if(is_null($status)){
            $data = $this->getDataSales();
            $this->dispatch('scan', ['product' => $data['product_detail'] ?? [], 'persentation' => $data['presentation_detail'] ?? [], 
                                'sales_detail' => $this->sales_detail, 'unidad_sat' => $data['unidad_sat'] ?? [],
                                'promotions' => $data['promotions'] ?? [], 'cant_sales_detail' => $data['cant_sales_detail'] ?? []]);
        }else{
            return true;
        }

    }

    // funcion para cambiar el stock del producto
    function setStock($presentation, $cant = 1, $type = 'menos'){
        // $presentacionese_existentes = PartToProduct::where('product_id', $presentation->product_id)->get();
        $product_existentes = Product::find($presentation->product_id);

        // if(count($presentacionese_existentes)){
        //     foreach($presentacionese_existentes as $item){
                // if($type == 'mas'){
                //     $val = $presentation->cantidad_despiezado > 0 ? $cant/($presentation->cantidad_despiezado):$cant;
                //     $item->stock = $item->stock + $val;
                // }else if($type == 'menos'){
                //     $val = $presentation->cantidad_despiezado > 0 ? 1/($presentation->cantidad_despiezado):1;
                //     $item->stock = $item->stock - $val;
                // }else{
                //     $val = $presentation->cantidad_despiezado > 0 ? $cant/($presentation->cantidad_despiezado):$cant;
                //     $item->stock = $item->stock - $val;
                // }
                // $item->save();
                if($type == 'mas'){
                    $val = $presentation->cantidad_despiezado > 0 ? $cant/($presentation->cantidad_despiezado):$cant;
                    $product_existentes->existence = $product_existentes->existence + $val;
                }else if($type == 'menos'){
                    $val = $presentation->cantidad_despiezado > 0 ? 1/($presentation->cantidad_despiezado):1;
                    $product_existentes->existence = $product_existentes->existence - $val;
                }else{
                    $val = $presentation->cantidad_despiezado > 0 ? $cant/($presentation->cantidad_despiezado):$cant;
                    $product_existentes->existence = $product_existentes->existence - $val;
                }
                $product_existentes->save();
        //     }
        // }
    }

// *****************************************************************

    //funcion para agregar manual la cantidad de productos
    public function updateCant($presentation_id, float $cant){  //seguirle aqui con las cantidades manuales

        $presentation = PartToProduct::find($presentation_id);

            $cantidad_sales_detail_cant = SaleDetailCant::where('sale_id', $this->id)
                ->where('part_to_product_id', $presentation->id)
                ->orderBy('created_at', 'desc')->get(); //cantidad del producto escaneado

  
            $detail_with_desc = SaleDetailCant::where('sale_id', $this->id)
                            ->where('part_to_product_id', $presentation->id)->where('descuento', '>', 0)->first();
            
            if(is_numeric($presentation->vigencia)){
                $presentation->vigencia += ($detail_with_desc->cant ?? 0);
            }     

            $arr_sales_detail_cant = $cantidad_sales_detail_cant;
            foreach($arr_sales_detail_cant as $index => $item){
                $this->destroyProduct($item->id, 'masivo');
            }
            
            if($presentation->cantidad_mayoreo > 0 && $cant >= $presentation->cantidad_mayoreo){
                $this->venta_mayoreo_save($presentation, $cant);
            }else{
                if(is_numeric($presentation->vigencia) && $presentation->vigencia >= $cant){
                    $this->menorAVigencia($presentation, $cant);
                }else if(is_numeric($presentation->vigencia) && $presentation->vigencia < $cant){ 
                    if(!is_null($presentation->vigencia) && $presentation->vigencia > 0){
                        $cant = $cant - $presentation->vigencia;
                        $presentation = PartToProduct::find($presentation->id);
                        $this->menorAVigencia($presentation, $presentation->vigencia);
                    }
                    
                    $sale_detail = $this->saveSaleDetail($presentation); //guardamos en save_detail
                    $sale_detail->saveNewCantDetails($sale_detail, $presentation, false, $cant);
                    $this->setStock($presentation, $cant, 'otro');
                    $this->calculoImpuestos($sale_detail, $presentation);
                }else if(!is_numeric($presentation->vigencia)){
                    $sale_detail = $this->saveSaleDetail($presentation); //guardamos en save_detail
                    $desc = $presentation->vigencia.' 23:59:59' > date('Y-m-d H:i:s') ? true:false;
                    $sale_detail->saveNewCantDetails($sale_detail, $presentation, $desc, $cant);
                    $this->setStock($presentation, $cant, 'otro');
                    $this->calculoImpuestos($sale_detail, $presentation);
                }

            }
                $this->data = $this->getDataSales();

                $this->dispatch('scan', ['cant' => $cant, 'product' => $this->data['product_detail'], 'persentation' => $this->data['presentation_detail'], 
                        'sales_detail' => $this->sales_detail, 'unidad_sat' => $this->data['unidad_sat'],
                        'promotions' => $this->data['promotions'], 'cant_sales_detail' => $this->data['cant_sales_detail']]);
    }

    //funcion para agregar nota al detalle de venta sobre stock de presentacion
    function stockOff($sale_detail_id, $code){
        $sale_detail = SaleDetail::find($sale_detail_id);
        if(is_object($sale_detail)){
            $sale_detail->notes = $sale_detail->notes.' *Presentación de producto sin existencia: '.$code;
            $sale_detail->save();
        }
    }

    //funcion para guardar la cantidad menor a la vigencia
    function menorAVigencia($presentation, $cant){
        $sale_detail = $this->saveSaleDetail($presentation);
        $withDesc = $presentation->vigencia > 0 ? true:false;
        
        if($presentation->tipo_descuento == 'porcentaje'){
            $descuento = (($presentation->monto_porcentaje*$presentation->price)/100)*$cant;
        }else{
            $descuento = ($presentation->monto_porcentaje*$cant);
        }
        
        $sale_detail->saveNewCantDetails($sale_detail, $presentation, true, $cant);

        $presentation->vigencia = ($presentation->vigencia ?? 0) - $cant;
        if($presentation->vigencia < 0){
            $presentation->vigencia = null;
        }
        $presentation->save();

        $this->setStock($presentation, $cant, 'otro');
        $this->calculoImpuestos($sale_detail, $presentation); //calculo de impuestos
    }

    //funcion para guardar el monto recibido, total venta y el cambio
    function cobrar($monto, $total_venta, $change){
        $sale = SaleModel::find($this->id);
        $sale->amount_received = $monto;
        $sale->total_sale = $total_venta;
        $sale->change = $change;
        $sale->status = 2;
        $sale->save();

        if($this->hasInternetConnection()){
            $ctrl = new \App\Http\Controllers\Controller();
            $ctrl->saveSaleDBExt($sale);
        }

        $this->dispatch('showTicket', ['sale_id' => $this->id]);
    }

    //funcion para saber si existe conexion a internet
    function hasInternetConnection(): bool
    {
        try {
            $connected = @fsockopen("www.google.com", 80);
            if ($connected) {
                fclose($connected);
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }
}
