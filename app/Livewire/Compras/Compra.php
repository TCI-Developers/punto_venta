<?php

namespace App\Livewire\Compras;

use Livewire\Component;
use App\Models\{Compra as CompraModel, Proveedor, Product};

class Compra extends Component
{   
    protected $listeners = [
        'vencimiento' => 'vencimiento',
        'entradaProductEdit' => 'entradaProductEdit',
        'recibidoProduct' => 'recibidoProduct',
    ];

    public $user;
    public $compra;
    public $compra_id;

    public $select_product = []; //select de productos
    public $product_arr = [];
    public $product_saved = [];
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
        $products = Product::get();

        if(!is_null($this->compra_id)){
            $this->compra = CompraModel::find($this->compra_id);
            $status = $this->compra->status == 1 ? '':'disabled';
            if(is_object($this->compra)){
                $this->product_saved = $this->compra->getDetalles;
                return view('livewire.compras.create', ['proveedores' => $proveedores,
                            'products' => $products, 'status' => $status]);
            }
        }

        return view('livewire.compras.create', ['products' => $products, 'proveedores' => $proveedores]);
    }

    //funcion para cuando seleccionan un producto se agrege en el cuerpo de la tabla
    public function selectProducts(){
        foreach($this->select_product ?? [] as $item){
            $product = Product::find($item);
            if(!isset($this->compra) || isset($this->compra) && $this->compra->hasProduct($item) == false){
                if(!isset($this->product_arr[$item])){
                    $this->product_arr[$product->id] = $product;
                }else{
                    unset($this->product_arr[(int)$item]);
                }
            }
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

    //funcion para calcular el subtotal
    public function entradaProductEdit($detalle_id, float $entrada, $precio_unitario, $tipo_impuesto, $impuesto){
       $this->subtotal[$detalle_id] = $entrada * $this->formatNumberr($precio_unitario);
        if($tipo_impuesto != 'SYS'){
           $this->valor_impuesto[$detalle_id] = ($this->subtotal[$detalle_id] * $impuesto);
        }
        $this->total[$detalle_id] = $this->subtotal[$detalle_id] + ($this->valor_impuesto[$detalle_id] ?? 0);
    }

    //funcion para calcular el subtotal con lo recibido
    public function recibidoProduct($detalle_id, float $recibido, $precio_unitario, $tipo_impuesto, $impuesto){
        $this->subtotal[$detalle_id] = $recibido * $this->formatNumberr($precio_unitario);
        if($tipo_impuesto != 'SYS'){
           $this->valor_impuesto[$detalle_id] = ($this->subtotal[$detalle_id] * $impuesto);
        }
        $this->total[$detalle_id] = $this->subtotal[$detalle_id] + ($this->valor_impuesto[$detalle_id] ?? 0);
    }

    //funcion para asignar la fecha de entrega a la orden de compra
    public function setFechaEntrega($fecha){
        $this->compra->programacion_entrega = $fecha;
        $this->compra->save();
    }

    //funcion para quitar signo de pesos y hacerlo numerico el valor
    function formatNumberr($valor){
        return (float)str_replace(',','', str_replace('$', '', $valor));
    }
}
