<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class PartToProduct extends Model
{
    use HasFactory;
    protected $table = 'parts_to_product';

    //Funcion para obtener la presentacion
    public function getPresentation(){
        return $this->hasOne('App\Models\PresentationProduct', 'id', 'presentation_product_id');
    }

    //obtenemos el producto
    public function getProduct(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }

    //obtenemos el producto
    public function getProducto($product_id){
        $product = Product::find($product_id);
        if(is_object($product)){
            return $product;
        }
        return false;
    }
}
