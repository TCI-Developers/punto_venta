<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer as CustomerModel;

class Customer extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate_cant = 25;
    public $search = '';
    public $status = 1;

    public function render()
    {   
        if($this->search == ''){
            $customers = CustomerModel::where('status', $this->status)->paginate($this->paginate_cant);
        }else{
            $customers = CustomerModel::where('status', $this->status)->where('name', 'LIKE', "%{$this->search}%")
            ->orWhere('razon_social', 'LIKE', "%{$this->search}%")
            ->orWhere('rfc', 'LIKE', "%{$this->search}%")
            ->orWhere('postal_code', 'LIKE', "%{$this->search}%")
                ->paginate($this->paginate_cant);
        }

        return view('livewire.customers.customer',['customers' => $customers]);
    }

    //funcion para abrir modal de edit
    public function btnEdit($customer_id){
        $customer = CustomerModel::find($customer_id);
        $this->dispatch('showModalEdit', ['customer' => $customer]);
    }

    //funcion para mostrar deshabilitados
    public function onOff(){
        $this->status = $this->status ? 0:1;
    }
}
