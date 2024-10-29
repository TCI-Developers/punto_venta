<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;
    protected $table = 'sales_detail';

    //Funcion para obtener marca (linea) del producto
    public function getPartToProduct(){
        return $this->hasOne('App\Models\PartToProduct', 'id', 'part_to_product_id');
    }

    //Funcion para obtener marca (linea) del producto
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
}
