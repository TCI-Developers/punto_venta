<?php
namespace App\Livewire\DevolucionCompra;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Devolucion, DevolucionMatriz};

class DevolucionIndexMatriz extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate_cant = 25;
    public $search = '';
    public $type = 'sale';
    public $activo_sale = 'active';
    public $activo_matriz = '';
    public $status;
    public $branch_id;

    public function mount($status, $branch_id){
        $this->status = $status;
        $this->branch_id = $branch_id;
    }

    public function render()
    {   
        if($this->search == ''){
            if($this->type == 'sale'){
                $devoluciones = Devolucion::where('status', $this->status)->where('branch_id', $this->branch_id)->orderBy('id', 'desc')->paginate($this->paginate_cant);
            }else{
                $devoluciones = DevolucionMatriz::where('status', $this->status)->where('branch_id', $this->branch_id)->orderBy('id', 'desc')
                                ->paginate($this->paginate_cant);
            }
        }else{
            if($this->type == 'sale'){
                $devoluciones = Devolucion::where('status', $this->status)->where('fecha_devolucion', 'LIKE', "%{$this->search}%")
                                ->orWhere('code_product', 'LIKE', "%{$this->search}%")->paginate($this->paginate_cant);
            }else{
                $devoluciones = DevolucionMatriz::where('status', $this->status)->where('fecha_devolucion', 'LIKE', "%{$this->search}%")
                                ->orWhere('code_product', 'LIKE', "%{$this->search}%")->paginate($this->paginate_cant);
            }
        }

        return view('livewire.devolucion_compra.index_devoluciones',['devoluciones' => $devoluciones]);
    }

    // funcion para cambiar de ventas a matriz
    public function pestaña($type){
        $this->type = $type;
        $this->activo_sale = $type == 'sale' ? 'active':'';
        $this->activo_matriz = $type == 'matriz' ? 'active':'';
    }
}
