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

    public function render()
    {   
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

    //funcion para obtener los precios
    public function getPrices($product_id){
        $product = Product::find($product_id);
        $prices = Price::where('product_id', $product_id)->get();
        $part_to_products = PartToProduct::where('product_id', $product_id)->where('status', 1)->get();
        $arr_presentations = [];

        if(count($part_to_products)){
            foreach($part_to_products as $index => $item){
                $arr_presentations[$index] = $item->getPresentation;
            }
        }
        $this->dispatch('modal_detail', ['prices' => $prices, 'product' => $product, 'part_to_products' => $part_to_products, 'arr_presentations' => $arr_presentations]);
    }

    public function updateMovDetail($id_mov_detail){
        $sale_detail = SaleDetail::find($id_mov_detail);
        $product_prices = $sale_detail->getPartToProduct->getProduct->getPrices;
        $part_to_products = PartToProduct::where('product_id', $sale_detail->getPartToProduct->product_id)->where('status', 1)->get();
        $arr_presentations = [];
        
        if(count($part_to_products)){
            foreach($part_to_products as $index => $item){
                $arr_presentations[$index] = $item->getPresentation;
            }
        }
        $this->dispatch('modal_detail_update', ['sale_detail' => $sale_detail, 'product_prices' => $product_prices, 'part_to_products' => $part_to_products, 'arr_presentations' => $arr_presentations]);
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
                $product = $presentation->getProducto($presentation->product_id);
                $this->saveDetail($presentation, $product, 'scan');
                $data = $this->getDataSales();

                $this->dispatch('scan', ['product' => $data['product_detail'], 'persentation' => $data['presentation_detail'], 
                                        'sales_detail' => $this->sales_detail, 'unidad_sat' => $data['unidad_sat'],
                                        'promotions' => $data['promotions'], 'cant_sales_detail' => $data['cant_sales_detail']]);
        }else{
            $this->dispatch('scan', ['error' => true]);
        }
    }

    //funcion para guardar el detalle de venta
    function saveDetail($presentation, $product, $type, $sale_detail_cant = null, $desc_stock = null){    
        if(is_object($sale_detail_cant)){
            $sale_detail_con = SaleDetail::find($sale_detail_cant->sale_detail_id);

            $this->addProduct($sale_detail_con, $presentation, $product, $sale_detail_cant->descuento, $desc_stock);
        }else{
            $sale_detail_con = SaleDetail::where('sale_id', $this->id)
            ->where('part_to_product_id', $presentation->id)
            ->where('unit_price', $presentation->price)->get();

            $index = count($sale_detail_con)-1; //obtenemos la ultima poscición del array           
            $this->addProduct($sale_detail_con[$index] ?? null, $presentation, $product);
        }

        if($type == 'scan'){
            $this->descStock($presentation);
        }
    }

    //funcion para actualizar registro si ya existe con el mismo precio
    function addProduct($sale_detail_con, $presentation, $product, $detail_desc = null, $desc_stock = null){
        if($sale_detail_con == null){
            $subtotal = $presentation->price;
            $iva = $product->taxes == 'IVA' ? ($presentation->price * $product->amount_taxes):0;
            $ieps = $product->taxes == 'IE3' ? ($presentation->price * $product->amount_taxes):0;
            $total = ($subtotal + $iva + $ieps);
            $cant = 1;

            $sale_detail = new SaleDetail(); //guardamos el detalle de venta
            $sale_detail->part_to_product_id = $presentation->id;
            $sale_detail->sale_id = $this->id;
            $sale_detail->unit_price = $presentation->price;
            $sale_detail->save();

            $sale_detail_cant = $sale_detail->saveNewCantDetails($sale_detail->id, $sale_detail->part_to_product_id); //guardamos cantidades
            $this->descuentos($presentation, $sale_detail, $sale_detail_cant);
            
            if(count($sale_detail->getCantSalesDetail)){
                foreach ($sale_detail->getCantSalesDetail as $item) {
                    $total -= $item->descuento;
                }
            }
        }else{            
            $sale_detail = $sale_detail_con;
            $cant = $sale_detail->getCantDetails($sale_detail->id, $sale_detail->part_to_product_id) + 1;
            $amount = $sale_detail->unit_price * $cant;
            $subtotal = $amount;

            $data = $this->calculoDatos($sale_detail, $product, $cant);
            $descuento = $detail_desc;
            dd($desc_stock && !$detail_desc, $desc_stock, !$detail_desc);
            if($desc_stock && !$detail_desc){ //acomodar qui el descuento de stock y vigencia
                $descuento = $this->descuentos($presentation, $sale_detail, null);
            }
                $cant_details = $sale_detail->saveCantDetails($sale_detail->id, $sale_detail->part_to_product_id, $descuento); //obtenemos las cantidades y descuentos
        }

        $sale_detail->amount = $data['amount'] ?? $subtotal;
        $sale_detail->iva = $data['iva'] ?? $iva;
        $sale_detail->ieps = $data['ieps'] ?? $ieps;
        $sale_detail->subtotal = $subtotal;

        if(isset($data['total'])){
            if(count($sale_detail_con->getCantSalesDetail)){
                foreach ($sale_detail_con->getCantSalesDetail as $item) {
                    $data['total'] -= $item->descuento;
                }
            }
        }

        $sale_detail->total = $data['total'] ?? $total;
        $sale_detail->save();

    }

    //funcion para aplicar descuentos
    function descuentos($presentation, $sale_detail, $sale_detail_cant = null){
        if($presentation->tipo_descuento == 'monto' || $presentation->tipo_descuento == 'porcentaje'){
            $presentation = PartToProduct::find($presentation->id);
            if(is_object($presentation)){
                $descuento = null;

                if($presentation->vigencia_cantidad_fecha == 'cantidad' && $presentation->vigencia > 0){ //cantidad y stock mayor a 0
                    $presentation->vigencia = $presentation->vigencia - 1;

                    if($presentation->tipo_descuento == 'porcentaje'){ 
                        $descuento = ($presentation->monto_porcentaje/100) * $presentation->price;
                    }else{
                        $descuento = $presentation->monto_porcentaje;
                    }

                }else if($presentation->vigencia_cantidad_fecha == 'fecha' && $presentation->vigencia.' 23:59:59' >= date('Y-m-d H:i:s')){ //fecha y fecha mayor a la actual
                    if($presentation->tipo_descuento == 'porcentaje'){
                        $descuento = ($presentation->monto_porcentaje/100) * $presentation->price;
                    }else{
                        $descuento = $presentation->monto_porcentaje;
                    }
                }
                
                $presentation->save();
                
                if(is_object($sale_detail_cant)){
                    $sale_detail_cant->total_descuento = $sale_detail_cant->cant * $descuento;
                    $sale_detail_cant->descuento = $sale_detail_cant->cant * $descuento;
                    $sale_detail_cant->save();
                }

                return $descuento;
            }
        }

        return null;
    }

    //funcion para agregar manual la cantidad de productos
    public function updateCant($sale_detail_cant_id, $cant){    
        $cant = (float)$cant;        
        $sale_detail_cant = SaleDetailCant::find($sale_detail_cant_id);
        $sale_detail = $sale_detail_cant->getSaleDetail;
        $presentation = $sale_detail->getPartToProductId($sale_detail->part_to_product_id);

        $cantidad = (float)$cant - 1;
        $desc_stock = false;
        
        if($sale_detail->nuevoReg($sale_detail_cant, $presentation)){
            $cantidad = (float)$cant;
        }

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

    //funcion para el calculo de iva, ieps, total
    function calculoDatos($sale_detail, $product, $cant){
        $data['amount'] = $sale_detail->unit_price * $cant;
        $data['iva'] = $sale_detail->iva == 0 ? 0:($sale_detail->unit_price * $product->amount_taxes);
        $data['ieps'] = $sale_detail->ieps == 0 ? 0:($sale_detail->unit_price * $product->amount_taxes);
        $data['total'] = $total = ($data['amount'] + $data['iva'] + $data['ieps']);

        return $data;
    }

    //funcion para calculo con promocion
    function calculoDatosPromo($sale_detail, $promotion, $cant, $item, $product){
        $modulo_cantidad = ($cant%$promotion->cantidad_producto); //obtenemos la cantidad sin de la promocion

        if($modulo_cantidad > 0){
            $data = $this->calculoDatos($item, $product, $modulo_cantidad);   
        }
        $new_cant = (int)($cant/$promotion->cantidad_producto); //obtenemos la cantidad de productos con promo
        $new_data = $this->calculoDatos($item, $product, $new_cant);

        $data['modulo_cantidad'] = $modulo_cantidad; //con esto sabemos si hay sobrantes aparte de la promo

        $data['amount'] = ($data['amount'] ?? 0) + $new_data['amount']; 
        $data['iva'] = ($data['iva'] ?? 0) + $new_data['iva']; 
        $data['ieps'] = ($data['ieps'] ?? 0) + $new_data['ieps']; 
        $data['total'] = ($data['total'] ?? 0) + $new_data['total']; 

        return $data;
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

    //funcion para eliminar un producto ya agregado en la venta
    public function destroyProduct($sale_detail_id){
        $sale_detail = SaleDetail::find($sale_detail_id);
        $sale_detail_cant = SaleDetailCant::where('sale_detail_id', $sale_detail_id)
                            ->where('part_to_product_id', $sale_detail->part_to_product_id)->first();
        
        if(is_object($sale_detail) && is_object($sale_detail_cant)){
            $presentation = PartToProduct::find($sale_detail->part_to_product_id);

            if(is_object($presentation)){
                    $presentation->stock = $presentation->stock + $sale_detail_cant->cant;
                    if($presentation->vigencia_cantidad_fecha == 'cantidad' && (int)$sale_detail_cant->descuento > 0){
                        $presentation->vigencia = $presentation->vigencia + $sale_detail_cant->cant;
                    }
                    $presentation->save();
            }

            $sale_detail_cant->delete();
            $sale_detail->delete();

            $data = $this->getDataSales();
            $this->dispatch('scan', ['tipo' => 'destroy', 'product' => $data['product_detail'] ?? '', 'persentation' => $data['presentation_detail'] ?? '', 
            'sales_detail' => $this->sales_detail, 'unidad_sat' => $data['unidad_sat'] ?? '']);
        }
    }

    //funcion para guardar sales_detail
    function saveDetailFunc($product, $presentation){
        $subtotal = $presentation->price;
        $iva = $product->taxes == 'IVA' ? ($presentation->price * $product->amount_taxes):0;
        $ieps = $product->taxes == 'IE3' ? ($presentation->price * $product->amount_taxes):0;
        $total = ($subtotal + $iva + $ieps);
        
        $descuento = $this->descuentos($presentation);

        $sale_detail = new SaleDetail();
        $sale_detail->part_to_product_id = $presentation->id;
        $sale_detail->sale_id = $this->id;
        $sale_detail->cant = 1;
        $sale_detail->unit_price = $presentation->price;
        $sale_detail->iva = $iva;
        $sale_detail->ieps = $ieps;
        $sale_detail->amount = $subtotal;
        $sale_detail->subtotal = $subtotal;
        $sale_detail->descuento = $descuento ?? 0;
        $sale_detail->total = $total;
        $sale_detail->save();
    }

    //funcion para descontar stock de presentacion
    function descStock($presentation){
        $presentation->stock = $presentation->stock - 1; //restamos al stock de la presentación
        $presentation->save();
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
