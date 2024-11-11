<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Sale as SaleModel, Customer, PaymentMethod, Product, Price, SaleDetail, PartToProduct, UnidadSat, Devolucion};
use Illuminate\Support\Facades\{Auth};
use Livewire\Attributes\Validate;

class Sale extends Component
{
    protected $listeners = [
        'getDate' => 'getDate',
        'updateCant' => 'updateCant',
        'destroyProduct' => 'destroyProduct',
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
            $this->total_sale = SaleDetail::where('sale_id', $this->id)->sum('total');
            $this->sales_detail = SaleDetail::where('sale_id', $this->id)->get();
            $devoluciones = Devolucion::where('sale_id', $this->id)->get();
            return view('livewire.sales.show', ['sale' => $sale, 'devoluciones' => $devoluciones]);
        }

        if($this->search != ''){
            $sales = SaleModel::where('user_id', Auth::User()->id)
                    ->where('folio', 'LIKE', "%{$this->search}%")
                    ->orWhereHas('paymentMethod', function($query) {
                        $query->where('pay_method', 'LIKE', "%{$this->search}%");
                    })
                    ->orderBy($this->whatDate, 'desc')->paginate($this->paginate_cant);
        }else{
            $sales = SaleModel::where('user_id', Auth::User()->id)
                    ->whereBetween($this->whatDate, $this->date)
                    // ->where('folio', 'LIKE', "%{$this->search}%")
                    ->orderBy($this->whatDate, 'desc')->paginate($this->paginate_cant);
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

                $this->saveDetail($presentation, $product);
                
                $data = $this->getDataSales();
               
                $this->dispatch('scan', ['product' => $data['product_detail'], 'persentation' => $data['presentation_detail'], 
                                        'sales_detail' => $this->sales_detail, 'unidad_sat' => $data['unidad_sat']]);
        }else{
            $this->dispatch('scan', ['error' => true]);
        }
    }

    //funcion para guardar el detalle de venta
    function saveDetail($presentation, $product){      
        $sale_detail_con = SaleDetail::where('sale_id', $this->id)
                            ->where('part_to_product_id', $presentation->id)
                            ->where('unit_price', $presentation->price)->get();

        if(count($sale_detail_con)){
            $this->addProduct($sale_detail_con, $presentation, $product);
        }else{
            $subtotal = $presentation->price;
            $iva = $product->taxes == 'IVA' ? ($presentation->price * $product->amount_taxes):0;
            $ieps = $product->taxes == 'IE3' ? ($presentation->price * $product->amount_taxes):0;
            $total = ($subtotal + $iva + $ieps);

            $sale_detail = new SaleDetail();
            $sale_detail->part_to_product_id = $presentation->id;
            $sale_detail->sale_id = $this->id;
            $sale_detail->cant = 1;
            $sale_detail->unit_price = $presentation->price;
            $sale_detail->iva = $iva;
            $sale_detail->ieps = $ieps;
            $sale_detail->amount = $subtotal;
            $sale_detail->subtotal = $subtotal;
            $sale_detail->total = $total;
            $sale_detail->save();
        }
    }

    //funcion para actualizar registro si ya existe con el mismo precio
    function addProduct($sale_detail_con, $presentation, $product){
        foreach($sale_detail_con as $item){
                $cant = $item->cant+1; //cantidad de productos
                $amount = $item->unit_price * $cant;
                $subtotal = $amount;
                $data = $this->calculoDatos($item, $product, $cant);

                $sale_detail_update = SaleDetail::find($item->id);
                $sale_detail_update->cant = $cant;
                $sale_detail_update->amount = $data['amount'];
                $sale_detail_update->iva = $data['iva'];
                $sale_detail_update->ieps = $data['ieps'];
                $sale_detail_update->subtotal = $subtotal;
                $sale_detail_update->total = $data['total'];
                $sale_detail_update->save();
        }
    }

    public function updateCant($sale_detail_id, $cant){
       $sale_detail = SaleDetail::find($sale_detail_id);
       $presentation = $sale_detail->getPartToProductId($sale_detail->part_to_product_id);
       $product = $sale_detail->getProductId($presentation->product_id);
       
       if(is_object($sale_detail)){
            $data = $this->calculoDatos($sale_detail, $product, $cant);

            $sale_detail->cant = $cant;
            $sale_detail->amount = $data['amount'];
            $sale_detail->subtotal = $data['amount'];
            $sale_detail->iva = $data['iva'];
            $sale_detail->ieps = $data['ieps'];
            $sale_detail->total = $data['total'];
            $sale_detail->save();

            $data = $this->getDataSales();

            $this->dispatch('scan', ['product' => $data['product_detail'], 'persentation' => $data['presentation_detail'], 
                                        'sales_detail' => $this->sales_detail, 'unidad_sat' => $data['unidad_sat']]);
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

    //funcion para obtener datos para pintar en tabla
    function getDataSales(){
        $this->sales_detail = SaleDetail::where('sale_id', $this->id)->get();
        $this->total_sale = SaleDetail::where('sale_id', $this->id)->sum('total');

        $data = [];
        if(count($this->sales_detail)){
            foreach($this->sales_detail as $index => $item){
                $data['presentation_detail'][] = $item->getPartToProductId($item->part_to_product_id);
                $data['presentation_aux'] = $item->getPartToProductId($item->part_to_product_id);
                $data['product_detail'][] = $item->getProductId($item->getPartToProductId($item->part_to_product_id)->product_id);
                
                if(isset($data['presentation_aux']->getPresentation->getUnidadSat->clave_unidad) && isset($data['presentation_aux']->getPresentation->getUnidadSat->name)){ //unidades sat
                    $data['unidad_sat'][$index] = $data['presentation_aux']->getPresentation->getUnidadSat->clave_unidad.' - '.$data['presentation_aux']->getPresentation->getUnidadSat->name;
                }
            }
        }
        return $data;
    }

    //funcion para eliminar un producto ya agregado en la venta
    public function destroyProduct($sale_detail_id){
        $sale_detail = SaleDetail::find($sale_detail_id);
        if(is_object($sale_detail)){
            $sale_detail->delete();

            $data = $this->getDataSales();
            $this->dispatch('scan', ['tipo' => 'destroy', 'product' => $data['product_detail'] ?? '', 'persentation' => $data['presentation_detail'] ?? '', 
            'sales_detail' => $this->sales_detail, 'unidad_sat' => $data['unidad_sat'] ?? '']);
        }
    }
}
