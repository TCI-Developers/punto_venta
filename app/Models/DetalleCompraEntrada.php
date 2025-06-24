<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompraEntrada extends Model
{
    use HasFactory;
    protected $table = 'detalle_compra_entradas';

    //funcion para guardar la entrada
    public function storeEntrada($entrada, $detalle_compra, $user_id){
        $detalle_compra_entrada = new DetalleCompraEntrada();
        $detalle_compra_entrada->entrada = $entrada;
        $detalle_compra_entrada->detalle_compra_id = $detalle_compra->id;
        $detalle_compra_entrada->user_id = $user_id;
        $detalle_compra_entrada->compra_id = $detalle_compra->compra_id;
        $detalle_compra_entrada->save();
    }

    //Funcion para obtener la compra
    public function getDetalleCompra(){
        return $this->hasOne('App\Models\DetalleCompra', 'id', 'detalle_compra_id');
    }
}
