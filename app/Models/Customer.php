<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customers';

    // public function setCustomers($customers) {
    //     if(count($customers)){
    //         foreach($customers as $item){
    //             $customer = Customer::find((int)$item->record_id_);
    //             if(!isset($customer)){
    //                 $customer = new Customer();
    //             }
    //             $customer->id = (int)$item->record_id_;
    //             $customer->name = $item->nombre;
    //             $customer->address1 = $item->direccion__street_1;
    //             $customer->address2 = $item->direccion__street_2;
    //             $customer->city = $item->direccion__city;
    //             $customer->state = $item->direccion__state_region;
    //             $customer->postal_code = $item->direccion__postal_code;
    //             $customer->country = $item->direccion__country;
    //             $customer->save();
    //         }
    //         return true;
    //     }
    //     return false;
    // }
}
