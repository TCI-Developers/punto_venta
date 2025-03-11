<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetailCant extends Model
{
    use HasFactory;
    protected $table = 'sales_detail_cant';

    //Funcion para obtener el detalle de venta
    public function getSaleDetail(){
        return $this->hasOne('App\Models\SaleDetail', 'id', 'sale_detail_id');
    }

    //Funcion para obtener el detalle de venta
    public function getPresentation(){
        return $this->hasOne('App\Models\PartToProduct', 'id', 'part_to_product_id');
    }

    //funcion para saber si es el mismo registro con descuento pero ya no hay stock de descuento
    public function newSaleDetailCant($saleDetailCant, $presentation){
        if((float)$saleDetailCant->descuento > 0 && $presentation->vigencia == 0 && $presentation->vigencia_cantidad_fecha == 'cantidad'){
            return true;
        }else if((float)$saleDetailCant->descuento > 0 && $presentation->vigencia.'23:59:59' < date('Y-m-d H:i:s') && $presentation->vigencia_cantidad_fecha == 'fecha'){
            return true;
        }
        return false;
    }
}
