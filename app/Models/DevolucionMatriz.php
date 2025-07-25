<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevolucionMatriz extends Model
{
    use HasFactory;
    protected $table = 'devoluciones_matriz';

    //Funcion para obtener la compra
    public function getCompra(){
        return $this->hasOne('App\Models\Compra', 'id', 'compra_id');
    }
    //funcion para obtener el producto
    public function getProduct(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }

}
