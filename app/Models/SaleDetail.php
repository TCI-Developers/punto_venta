<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{PartToProduct, SaleDetailCant};

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

    //funcion para obtener las cantidades de los detalles de venta
    public function getCantDetails($sale_detail_id, $part_to_product_id){
        $cant_details = SaleDetailCant::where('sale_detail_cant', $sale_detail_id)
                                        ->where('part_to_product_id', $part_to_product_id)->get();
        $data = [];                     
        if(count($cant_details)){
            foreach($cant_details as $item){
                $data['cant'] += $item->cant;
                $data['descuento'] += $item->descuento ?? 0;
            }
        }

        return $data;
    }

    //Funcion para obtener producto
    public function getProductId($id){
        $product = Product::find($id);
        if(is_object($product)){
            return $product;
        }
        return null;
    }

    //funcion para saber si es el mismo registro con descuento
    public function mismoRegDescuento($saleDetail, $presentation){
        if($saleDetail->descuento > 0 && $presentation->vigencia > 0 && $presentation->vigencia_cantidad_fecha == 'cantidad'){
            return true;
        }else if($saleDetail->descuento > 0 && $presentation->vigencia.'23:59:59' >= date('Y-m-d H:i:s') && $presentation->vigencia_cantidad_fecha == 'fecha'){
            return true;
        }
        return false;
    }

    //funcion para saber si es el mismo registro con descuento
    public function mismoSinRegDescuento($saleDetail, $presentation){
        if($saleDetail->descuento == 0 && $presentation->vigencia == 0 && $presentation->vigencia_cantidad_fecha == 'cantidad'){
            return true;
        }else if($saleDetail->descuento == 0 && $presentation->vigencia.'23:59:59' < date('Y-m-d H:i:s') && $presentation->vigencia_cantidad_fecha == 'fecha'){
            return true;
        }
        return false;
    }

    //funcion para saber si es el mismo registro con descuento pero ya no hay stock de descuento
    public function nuevoReg($saleDetail, $presentation){
        if($saleDetail->descuento > 0 && $presentation->vigencia == 0 && $presentation->vigencia_cantidad_fecha == 'cantidad'){
            return true;
        }else if($saleDetail->descuento > 0 && $presentation->vigencia.'23:59:59' < date('Y-m-d H:i:s') && $presentation->vigencia_cantidad_fecha == 'fecha'){
            return true;
        }
        return false;
    }

}
