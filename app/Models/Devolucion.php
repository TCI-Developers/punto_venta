<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    use HasFactory;
    protected $table = 'devoluciones';

    //Funcion para obtener producto
    public function getProduct(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }

    //Funcion para obtener producto
    public function getPartToProduct(){
        return $this->hasOne('App\Models\PartToProduct', 'id', 'part_to_product_id');
    }
}
