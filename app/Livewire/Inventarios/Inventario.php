<?php

namespace App\Livewire\Inventarios;

use Livewire\Component;
use App\Models\{Product, Brand, InventoryAdjustments};
use Illuminate\Support\Facades\{Auth};


use function PHPUnit\Framework\isEmpty;

class Inventario extends Component
{   
    protected $listeners = [
        'getProducts' => 'getProducts',
    ];

    public $products;
    public $lineas_id = []; 
    public $linea_id;
    public $status = 1;
    public $inputsNewStock = [];

    public $line_id = '';

    public function mount(){
        $this->products = collect();
        $this->lineas_id = $this->getProductsDisabled()['lineas_id'];
        $this->status = count($this->lineas_id) ? 0:1;
    }

    public function render()
    {   
        if(count($this->getProductsDisabled()['products'])){
            $this->dispatch('refreshTable', ['products' => $this->getProductsDisabled()['products'], 'lineas' => $this->lineas_id, 'status' => $this->status]);
        }
        $brands = Brand::where('status', 1)->get();
        return view('livewire.invetarios.invetario', ['lineas' => $brands]);
    }

    // función para obtener los productos de las líneas seleccionadas
    public function getProducts($line_id)
    {   
        if($line_id != ''){
            $this->products = [];
            array_push($this->lineas_id, (int)$line_id);
        }else{
            $this->lineas_id = $this->remover($this->lineas_id, $this->linea_id['value']);
        }
        $this->products = Product::with('getBrand')->whereIn('brand_id', $this->lineas_id)->get();
        $this->dispatch('refreshTable', ['products' => $this->products, 'lineas' => $this->lineas_id, 'status' => $this->status]);
    }

    public function getProductsDisabled()
    {   
        $data['products'] = Product::with('getBrand')->where('activo', 0)->get();
        $data['lineas_id'] = $data['products']->pluck('brand_id')->unique()->values()->toArray();
        return $data;
    }

    public function bloquearLinea()
    { 
        $this->status = $this->status == 1 ? 0 : 1;
        if (count($this->lineas_id)) {
            $this->products = Product::whereIn('brand_id', $this->lineas_id)->get();
            foreach ($this->products as $item) {
                $product = Product::find($item->id);
                if (is_object($product)) {
                    $product->activo = $this->status;
                    $product->save();
                }
            }
        } else {
            $this->products = Product::where('activo', 0)->get();
        }

        $this->dispatch('showInputs', ['status' => $this->status]);
    }

    private function remover($arr,$valor)
    {
        foreach ($arr ?? [] as $index => $item) 
        {
            if($item == (int)$valor){
                unset($arr[$index]);
                break;
            }
        }
        return array_values($arr);
    }

    function saveNewStock(){
        try {
            foreach($this->inputsNewStock as $id => $item){
                $product = Product::find($id);
                if(is_object($product)){
                    
                    $inventory = new InventoryAdjustments();
                    $inventory->product_id = $product->id;
                    $inventory->user_name = Auth::User()->name;
                    $inventory->old_stock = $product->existence;
                    $inventory->new_stock = $item;
                    $inventory->difference = ($item - $product->existence);
                    $inventory->save();
                    
                    $product->existence = $item;
                    $product->save();
                }
            }    
            $this->dispatch('alert', ['status' => 'success', 'message' => count($this->inputsNewStock).' Existencias actualizadas con exito.']);
        } catch (\Throwable $th) {
            $this->dispatch('alert', ['status' => 'error', 'message' => 'No se pudo completar la accion con exito.']);
        }

    }
}
