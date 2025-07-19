<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevolucionMatriz extends Model
{
    use HasFactory;
    protected $table = 'devoluciones_matriz';

    //Funcion para obtener producto
    // public function getProduct(){
    //     return $this->hasOne('App\Models\Sale', 'id', 'sale_id');
    // }

    // //Funcion para obtener el usuario
    // public function getUser(){
    //     return $this->hasOne('App\Models\User', 'id', 'user_dev');
    // }

    // //Funcion para obtener producto
    // public function getPartToProduct(){
    //     return $this->hasOne('App\Models\PartToProduct', 'id', 'part_to_product_id');
    // }

    // //Funcion para saber si tiene el codigo de producto en devolucion
    // public function hasCodeProduct($sale_details_dev, $codeProduct){
    //     if(count($sale_details_dev)){
    //         foreach($sale_details_dev as $item){
    //             if($codeProduct == $item->getPartToProduct->getProduct->code_product){
    //                 return true;
    //                 break;
    //             }
    //         }
    //     }
    //     return false;
    // }
}
