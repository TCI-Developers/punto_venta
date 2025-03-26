<?php

namespace App\Livewire\Compras;

use Livewire\Component;
use App\Models\{Compra as CompraModel, Proveedor, Product};

class Compra extends Component
{   
    protected $listeners = [
        'vencimiento' => 'vencimiento',
    ];

    public $user;
    public $compra_id;

    public $select_product; //select de productos
    public $product_arr = [];
    public $entrada_product = [];
    public $subtotal = [];
    public $valor_impuesto = [];
    public $total = [];

    public function mount($compra_id, $user){
        $this->compra_id = $compra_id;
        $this->user = $user;
    }

    public function render()
    {   
        $proveedores = Proveedor::where('status', 1)->orderBy('name', 'asc')->get();
        if(!is_null($this->compra_id)){
            $compra = CompraModel::find($this->compra_id);
            $products = Product::where('branch_id', $this->user->branch_id)->get();

            if(is_object($compra)){
                return view('livewire.compras.create', ['compra' => $compra, 'proveedores' => $proveedores,
                            'products' => $products]);
            }
        }

        return view('livewire.compras.create', ['proveedores' => $proveedores]);
    }

    //funcion para cuando seleccionan un producto se agrege en el cuerpo de la tabla
    public function selectProducts(){
        $this->product_arr = [];
        if(count($this->select_product)){
            foreach($this->select_product as $item){
                $product = Product::find($item);
                $this->product_arr[$product->id] = $product;
            }
            $this->dispatch('selectRefresh');
        }
    }

    //funcion para calcular el subtotal
    public function entradaProduct($product_id, float $entrada){
       $this->subtotal[$product_id] = $entrada * $this->product_arr[$product_id]->precio;
        if($this->product_arr[$product_id]->taxes != 'SYS'){
           $this->valor_impuesto[$product_id] = ($this->subtotal[$product_id] * $this->product_arr[$product_id]->amount_taxes);
        }
        $this->total[$product_id] = $this->subtotal[$product_id] + ($this->valor_impuesto[$product_id] ?? 0);
    }
}
