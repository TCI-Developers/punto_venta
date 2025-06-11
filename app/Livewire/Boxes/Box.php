<?php

namespace App\Livewire\Boxes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Box as BoxModel, Devolution};
use Illuminate\Support\Facades\{DB,Auth};

class Box extends Component
{   
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate_cant = 25;
    public $user = '';
    // public $search = '';

    public function render()
    {   
        $this->user = Auth::User();   
        $current_date = date('Y-m-d').' 23:59:59';
        // if($this->search == ''){
        //     $this->boxes = BoxModel::where('end_date', '<=' ,$current_date)->orderBy('end_date', 'asc')->paginate($this->paginate_cant);
        // }else{
            $boxes = BoxModel::where('end_date', '<=' ,$current_date)->orderBy('end_date', 'asc')->paginate($this->paginate_cant);
        // }

        return view('livewire.boxes.box', ['boxes' => $boxes]);
    }

    //funcion para abrir el modal de denominaciones y obtener el registro del user
    public function openModalMoney($box_id){
        $box = BoxModel::find($box_id)->toArray();
        $this->dispatch('openModalMoney', ['box' => count($box) ? $box:null, 'status' => count($box) ? 1:0]);
    }
}
