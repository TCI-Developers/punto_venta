<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PartToProduct;

class SaleDetail extends Model
{
    use HasFactory;
    protected $table = 'sales_detail';

    //Funcion para obtener producto por presentacion
    public function getPartToProduct(){
        return $this->hasOne('App\Models\PartToProduct', 'id', 'part_to_product_id');
    }

    //Funcion para obtener producto por presentacion
    public function getPartToProductId($id){
        $part_to_product = PartToProduct::find($id);
        if(is_object($part_to_product)){
            return $part_to_product;
        }
        return null;
    }

    //Funcion para obtener producto
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }

    //Funcion para obtener producto
    public function getProductId($id){
        $product = Product::find($id);
        if(is_object($product)){
            return $product;
        }
        return null;
    }

    //funcion para saber si tiene descuento
    public function hasDescuento(){
        // $sale_detail_con = $this::where('sale_id', $this->id)
        //                     ->where('part_to_product_id', $presentation->id)
        //                     ->where('unit_price', $presentation->price)->get();
        
    }

}
