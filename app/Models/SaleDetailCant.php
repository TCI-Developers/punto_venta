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
}
