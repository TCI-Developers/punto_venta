<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\{DB,Auth};
use App\Models\{Product as ProductModel, PresentationProduct, PartToProduct, UnidadSat, Promotion};

class Product extends Component
{   
    protected $listeners = [
        'value_product_id' => 'value_product_id'
    ];

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate_cant = 25;
    public $search = '';
    public $presentations = [];
    public $promotions = [];
    public $part_to_products = [];
    public $unidades_sat = [];

    public $product_id = '';
    public $part_product_id = '';
    public $modal_presentation_type_id = '';
    public $modal_promotion_id = '';
    public $modal_price = '';
    public $modal_code_bar = '';

    public $modal_tipo_descuento = 'monto';
    public $modal_monto_porcentaje = '';
    public $modal_vigencia_cantidad_fecha = 'fecha';
    public $modal_vigencia_fecha = '';
    public $modal_vigencia_cantidad = '';

    public function mount(){
        $this->presentations = PresentationProduct::where('status', 1)->get();
        $this->promotions = Promotion::where('status', 1)->get();
        $this->unidades_sat = UnidadSat::where('status', 1)->get();
        $this->modal_vigencia_fecha = date('Y-m-d');
    }

    public function render()
    {   
        if($this->search == ''){
            $products = ProductModel::where('activo', 1)->paginate($this->paginate_cant);
        }else{
            $products = ProductModel::where('code_product', 'LIKE', "%{$this->search}%")
                ->orWhere('description', 'LIKE', "%{$this->search}%")
                ->orWhere('unit', 'LIKE', "%{$this->search}%")
                ->orWhere('existence', 'LIKE', "%{$this->search}%")
                ->paginate($this->paginate_cant);
               
        }

        return view('livewire.products.product',['products' => $products]);
    }

    //funcion para obtener el product_id, asignamos si el modal actualiza o crear
    function value_product_id($product_id, $status){
        $this->product_id = (int)$product_id;
        $this->part_product_id = '';
        $this->modal_presentation_type_id = '';
        $this->modal_promotion_id = '';
        $this->modal_price = '';
        $this->modal_code_bar = '';
        $this->part_to_products = PartToProduct::where('product_id', (int)$product_id)->where('status', $status)->get();
        // dd($this->product_id, $product_id);
        $arr_presentations = [];
        if(count($this->part_to_products)){
            foreach($this->part_to_products as $index => $item){
                $arr_presentations[$index] = $item->getPresentation->type;
            }
        }
        $this->dispatch('table_modal', ['part_to_products' => $this->part_to_products, 'presentation_name' => $arr_presentations , 'status' => $status, 'id' => $this->product_id]);
    }

    //funcion para guardar la presentacion del producto
    function save_presentation_in_product(){
       

        $arr_inputs[0] = $this->modal_presentation_type_id != '' ? $this->modal_presentation_type_id:'presentation_type_id';
        $arr_inputs[1] = $this->modal_price != '' ? $this->modal_price:'price';
        $arr_inputs[2] = $this->modal_code_bar != '' ? $this->modal_code_bar:'code_bar';
        $this->alert($arr_inputs, 'error');

        // dd($this->modal_tipo_descuento, $this->modal_monto_porcentaje, $this->modal_vigencia_cantidad_fecha, $this->modal_vigencia_fecha, $this->modal_vigencia_cantidad );
       if($this->modal_presentation_type_id != '' && $this->modal_price != '' && $this->modal_code_bar != ''){
           if($this->part_product_id == ''){
               $presentation = new PartToProduct();
               $presentation->product_id = (int)$this->product_id;
               $status_alert = 'success';    
            }else{
                $presentation = PartToProduct::find($this->part_product_id);
                $status_alert = 'update';
            }

            if($this->saveDescuentos()){
                $presentation->tipo_descuento = $this->modal_tipo_descuento;
                $presentation->monto_porcentaje = $this->modal_monto_porcentaje;
                $presentation->vigencia_cantidad_fecha = $this->modal_vigencia_cantidad_fecha;
                $presentation->vigencia = $this->modal_vigencia_cantidad_fecha == 'fecha' ? $this->modal_vigencia_fecha:$this->modal_vigencia_cantidad;
            }
            
            $presentation->presentation_product_id = (int)$this->modal_presentation_type_id;
            $presentation->price = $this->modal_price;
            $presentation->code_bar = $this->modal_code_bar;
            $presentation->save();
            $this->value_product_id($this->product_id, 1);
            $this->dispatch('alert', ['input' => [], 'type' => $status_alert]);
       }
    }

    //funcion para guardar los descuentos
    function saveDescuentos(){
        if($this->modal_monto_porcentaje != '' && $this->modal_monto_porcentaje > 0){
            if($this->modal_vigencia_cantidad_fecha == 'fecha'){
                $arr_inputs[0] = date($this->modal_vigencia_fecha) > date('Y-m-d') ? $this->modal_vigencia_fecha:'vigencia_fecha';
            }else{
                $arr_inputs[0] = $this->modal_vigencia_cantidad > 0 ? $this->modal_vigencia_cantidad:'vigencia_cantidad';
            }

            $this->alert($arr_inputs, 'error_descuento');

            if($arr_inputs[0] == 'vigencia_cantidad' || $arr_inputs[0] == 'vigencia_fecha'){
                return false;
            }else{
                return true;
            }

        }else{
            return false;
        }
    }

    //funcion para mostrar en el modal algun campo vacio
    function alert($inputs, $type){
       if($type == 'error'){ 
            for ($i=0; $i <count($inputs) ; $i++) { 
                if($inputs[$i] == 'presentation_type_id' || $inputs[$i] == 'presentation_type_id' || $inputs[$i] == 'presentation_type_id' || $inputs[$i] == 'vigencia_cantidad'){
                    $this->dispatch('alert', ['input' => $inputs, 'type' => $type]);
                    break;
                }
            }
        }else if($type == 'error_descuento'){
            for ($i=0; $i <count($inputs) ; $i++) { 
                if($inputs[$i] == 'vigencia_cantidad' || $inputs[$i] == 'vigencia_fecha'){
                    $this->dispatch('alert', ['input' => $inputs, 'type' => $type]);
                    break;
                }
            }
        }else{
            $this->dispatch('alert', ['input' => $inputs, 'type' => $type]);
        }
    }

    //funcion para eliminar presentacion de producto
    function deletePresentation($id){
        $part_to_product = PartToProduct::find($id);
        if(is_object($part_to_product)){
            $part_to_product->status = 0;
            $part_to_product->save();

            $this->value_product_id($part_to_product->product_id, 1);
            $this->alert('', 'delete');
        }
    }

    //funcion para editar presentacion de producto
    function showPresentation($id){
        $this->part_product_id = $id;
        $part_to_product = PartToProduct::find($id);
        if(is_object($part_to_product)){
            $this->modal_presentation_type_id = $part_to_product->presentation_product_id;
            $this->modal_price = $part_to_product->price;
            $this->modal_code_bar = $part_to_product->code_bar;

            //descuento
            $this->modal_tipo_descuento =  $part_to_product->tipo_descuento;
            $this->modal_monto_porcentaje =  $part_to_product->monto_porcentaje;
            $this->modal_vigencia_cantidad_fecha = $part_to_product->vigencia_cantidad_fecha;
            $this->modal_vigencia_fecha =  is_numeric($part_to_product->vigencia) ? date('Y-m-d'):$part_to_product->vigencia;
            $this->modal_vigencia_cantidad =  is_numeric($part_to_product->vigencia) ? $part_to_product->vigencia:'';
        //    dd($part_to_product);
            $this->dispatch('showModalEdit', ['part_to_product' => $part_to_product]);
        }
    }
}
