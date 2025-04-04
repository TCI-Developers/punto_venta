<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    use HasFactory;
    protected $table = 'detalles_compra';

    //Funcion para obtener la compra
    public function getCompra(){
        return $this->hasOne('App\Models\Compra', 'id', 'compra_id');
    }

    //funcion para obtener los datos del producto
    public function getProduct(){
        return $this->hasOne('App\Models\Product', 'id', 'producto_id');
    }

    //Funcion para obtener la compra
    public function getEntrada(){
        return $this->hasOne('App\Models\DetalleCompraEntrada', 'detalle_compra_id', 'id')->latest('created_at');
    }
}
