<?php
namespace App\Livewire\DevolucionCompra;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Compra;

class DevolucionCompra extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate_cant = 25;
    public $search = '';

    public function render()
    {   
        if($this->search == ''){
            $compras = Compra::where('status_devolucion', 0)->orderBy('folio', 'desc')->paginate($this->paginate_cant);
        }else{
            $compras = Compra::where('status_devolucion', 0)->where('folio', 'LIKE', "%{$this->search}%")
            ->orWhere('user', 'LIKE', "%{$this->search}%")
            ->paginate($this->paginate_cant);
        }

        return view('livewire.devolucion_compra.index',['compras' => $compras]);
    }
}
