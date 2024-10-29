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
            $sales_detail = SaleDetail::where('sale_id', $this->id)->get();
            $devoluciones = Devolucion::where('sale_id', $this->id)->get();
            return view('livewire.sales.show', ['sale' => $sale, 'sales_detail' => $sales_detail, 'devoluciones' => $devoluciones]);
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
}
