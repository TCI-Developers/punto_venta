<?php
namespace App\Livewire\DevolucionCompra;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Compra, DetalleCompra, Driver};

class DevolucionShowMatriz extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate_cant = 25;
    public $search = '';
    public $compra_id = '';
    public $drivers = [];

    public function mount($compra_id){
        $this->compra_id = $compra_id;
    }

    public function render()
    {   
        $compra = Compra::find($this->compra_id);
        $this->drivers = Driver::where('status', 1)->get();
        if($this->search == ''){
            $compra_detalles = $compra->getDetalles;
        }else{
            $compra_detalles = DetalleCompra::where('status', 1)->where('taxes', 'LIKE', "%{$this->search}%")
            ->orWhere('code_product', 'LIKE', "%{$this->search}%")
            ->paginate($this->paginate_cant);
        }

        return view('livewire.devolucion_compra.show',['compra' => $compra, 'compra_detalles' => $compra_detalles]);
    }
}
