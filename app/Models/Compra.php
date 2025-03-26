<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
