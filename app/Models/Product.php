<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Price, Product as ProductModel, Brand};
use Illuminate\Support\Facades\{DB,Auth};

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [
        'id',
        'code_product',
        'description',
        'barcode',
        'image_path',
        'taxes',
        'amount_taxes',
        'unit',
        'unit_description',
        'existence',
        'precio',
        'precio_mayoreo',
        'precio_despiece',
        'comments',
        'brand_id',
    ];

    //funcion para guardar todos los productos de quick en local
    public function setProducs($products, $branchId = null) {
        $branch_id = is_null($branchId) ? Auth::User()->branch_id:$branchId;

        if(count($products) && $branch_id){
            foreach($products as $item){
                $product = ProductModel::find((int)$item->{'record_id#'});
                if(!isset($product)){
                    $product = new ProductModel();
                }               
                
                $brand = Brand::find((int)$item->{'linea___record_id#'});
                if(is_object($brand)){
                    $product->id = (int)$item->{'record_id#'};
                    $product->code_product = $item->codigo_del_producto;
                    $product->description = $item->descripcion;
                    $product->barcode = $item->codigo_barras;
                    $product->unit = $item->unidad;
                    $product->unit_description = $item->{'unidad_sat___descripción'};
                    $product->existence = $item->existencia_real;
                    $product->taxes = $item->impuesto;

                    $product->precio = (float)$item->preciov_1;
                    $product->precio_mayoreo = (float)$item->preciov_3;
                    $product->precio_despiece = (float)$item->preciov_4;
                    
                    $product->amount_taxes = (float)$item->valor_impuesto;
                    $product->activo = $item->baja == 0 ? 1:0;
                    $product->comments = $item->notas;
                    $product->brand_id = (int)$item->{'linea___record_id#'};
                    $product->branch_id = $branch_id;
                    $product->save();
                }
            }
            return true;
        }
        return false;
    }

    //Funcion para obtener marca (linea) del producto
    public function getBrand(){
        return $this->hasOne('App\Models\Brand', 'id', 'brand_id');
    }

    //Funcion para obtener presentacion
    public function getPartToProduct(){
        return $this->hasOne('App\Models\PartToProduct', 'product_id', 'id');
    }

    //Funcion para obtener presentacion
    public function getPartToProducts(){
        return $this->hasMany('App\Models\PartToProduct', 'product_id', 'id');
    }

    //Funcion para obtener presentacion que son despiezado
    public function getPartToProductDespiezado(){
        return $this->hasOne('App\Models\PartToProduct', 'product_id', 'id')->where('cantidad_despiezado', '>', 0);
    }

    //Funcion para obtener presentacion que son despiezado
    public function getPartToProductDespiezados(){
        return $this->hasMany('App\Models\PartToProduct', 'product_id', 'id')->where('cantidad_despiezado', '>', 0);
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
