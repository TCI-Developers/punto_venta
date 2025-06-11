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

    //funcion para obtener las devoluciones por sale_id
    public function getDevoluciones(){
        return $this->hasMany('App\Models\Devolucion', 'sale_id', 'sale_id');
    }

    //Funcion para obtener sales_detail_cant
    public function getCantSalesDetail(){
        return $this->hasMany('App\Models\SaleDetailCant', 'sale_detail_id', 'id')->where('status', 1);
    }

    //Funcion para obtener sales_detail_cant
    public function getCantSalesDetailDev(){
        return $this->hasMany('App\Models\SaleDetailCant', 'sale_detail_id', 'id')->where('status', 0);
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
        if((float)$saleDetail->descuento > 0 && $presentation->vigencia == 0 && $presentation->vigencia_cantidad_fecha == 'cantidad'){
            return true;
        }else if((float)$saleDetail->descuento > 0 && $presentation->vigencia.'23:59:59' < date('Y-m-d H:i:s') && $presentation->vigencia_cantidad_fecha == 'fecha'){
            return true;
        }
        return false;
    }

    //funcion para guardar cantidades y descuentos de los detalles de venta validando si ya existe alguno
    function saveCantDetails($sale_detail_id, $part_to_product_id, $descuento){
        $cant_details = SaleDetailCant::where('sale_detail_id', $sale_detail_id)
                                        ->where('part_to_product_id', $part_to_product_id)->get();
           
        if(count($cant_details)){
            $ban = false;
            $descuento = $descuento == null ? 0.0:$descuento;
            foreach($cant_details as $index => $item){

                if((float)$item->descuento === $descuento){
                    $cant_detail = SaleDetailCant::find($item->id);
                    $cant_detail->cant += 1;
                    $cant_detail->descuento = round($descuento, 2);
                    $cant_detail->total_descuento = round(($cant_detail->cant * $descuento), 2);
                    $cant_detail->save();

                    $ban = true;
                    break;
                }
            }

            if($ban == false){
                $this->saveNewCantDetails($sale_detail_id, $part_to_product_id);
            }

            return $cant_details ?? null;
        }
    }

    //funcion para guardar cantidades y descuentos de los detalles de venta
    function saveNewCantDetails($sale_detail, $presentation, $with_desc = true, $cant = 1){
        if($presentation->tipo_descuento == 'porcentaje'){
            $descuento = (($presentation->monto_porcentaje*$presentation->price)/100)*$cant;
        }else{
            $descuento = ($presentation->monto_porcentaje*$cant);
        }

        $detail_cant = new SaleDetailCant();
        $detail_cant->sale_detail_id = $sale_detail->id;
        $detail_cant->part_to_product_id = $presentation->id;
        $detail_cant->sale_id = $sale_detail->sale_id;
        $detail_cant->cant = $cant;
        $detail_cant->descuento = $with_desc ? (round($presentation->monto_porcentaje,2) ?? 0):0;
        $detail_cant->total_descuento = $with_desc ? (round($descuento,2) ?? 0):0;
        $detail_cant->save();

        return $detail_cant;
    }

    //funcion para obtener las cantidades de los detalles de venta
    function getCantDetails($sale_detail_id, $part_to_product_id){
        $cant_details = SaleDetailCant::where('sale_detail_id', $sale_detail_id)
                                        ->where('part_to_product_id', $part_to_product_id)->get();
        $cant = 0;
        if(count($cant_details)){
            foreach($cant_details as $index => $item){
                $cant += $item->cant;
            }
        }

        return $cant;
    }

}
