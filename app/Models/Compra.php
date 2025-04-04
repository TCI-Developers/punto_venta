<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DetalleCompra;

class Compra extends Model
{
    use HasFactory;
    protected $table = 'compras';

    //funcion para asignar folio a la venta
    public function addFolio($compra_id){
        $compra = Compra::find($compra_id);
        if(is_object($compra)){
            return $compra->tipo.'-'.$compra->id; 
        }

        return false;
    }

    //Funcion para obtener el proveedor
    public function getProveedor(){
        return $this->hasOne('App\Models\Proveedor', 'id', 'proveedor_id');
    }

    //Funcion para obtener los detalles
    public function getDetalles(){
        return $this->hasMany('App\Models\DetalleCompra', 'compra_id', 'id')->where('status', 1);
    }

    //Funcion para obtener los detalles
    public function hasProduct($product_id){
        $detalle = DetalleCompra::where('producto_id', $product_id)->first();
        return isset($detalle->id) ? true:false;
    }
}
