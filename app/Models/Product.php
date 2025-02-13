<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Price, Product as ProductModel, Brand};
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    //funcion para guardar todos los productos de quick en local
    public function setProducs($products) {
        if(count($products)){
            foreach($products as $item){
                $product = ProductModel::find((int)$item->record_id_);
                if(!isset($product)){
                    $product = new ProductModel();
                }               
                
                $brand = Brand::find((int)$item->linea___record_id_);
                if(is_object($brand)){
                    $product->id = (int)$item->record_id_;
                    $product->code_product = $item->codigo_del_producto;
                    $product->description = $item->descripcion;
                    $product->barcode = $item->codigo_barras;
                    $product->unit = $item->unidad;
                    $product->unit_description = $item->unidad_sat___descripci_n;
                    $product->existence = $item->existencia_real;
                    $product->taxes = $item->impuesto;
                    $product->amount_taxes = (float)$item->valor_impuesto;
                    $product->activo = $item->baja == 0 ? 1:0;
                    $product->comments = $item->notas;
                    $product->brand_id = (int)$item->linea___record_id_;
                    $product->branch_id = (int)Auth::User()->branch_id;
                    $product->save();
                
                    $con=1;
                    for ($i=1; $i <=4 ; $i++) { 
                        $price_v = 'preciov_'.$i; 
                        if($item->$price_v != 0){
                            $this->setPrice((int)$item->record_id_, $item->$price_v, $con);
                            $con++;
                        }   
                    }
                }
                    // break;
            }
            return true;
        }
        return false;
    }

    //Funcion para obtener marca (linea) del producto
    public function getBrand(){
        return $this->hasOne('App\Models\Brand', 'id', 'brand_id');
    }

    //Funcion para obtener los precios 
    public function getPrices(){
        return $this->hasMany('App\Models\Price', 'product_id', 'id');
    }

    //Funcion para guardar el precio
    public function setPrice($product_id, $price, $con){
        $price_ = Price::where('product_id', $product_id)->where('price', $price)->first();
            if(!isset($price_)){
                $price_ = new Price();
            } 
        $price_->product_id = $product_id;
        $price_->price = $price;
        $price_->type = $con;
        $price_->save();
    }
}
