<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
Use App\Models\{SaleDetail};

class Sale extends Model
{
    use HasFactory;
    protected $table = 'sales';

    //Funcion para obtener marca (linea) del producto
    public function getClient(){
        return $this->hasOne('App\Models\Customer', 'id', 'customer_id');
    }

    //Funcion para obtener el usuario que creo la venta
    public function getUser(){
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    
    //Funcion para obtener marca (linea) del producto
    public function getDetails(){
        return $this->hasMany('App\Models\SaleDetail', 'sale_id', 'id')->where('status', 1);
    }
    //funcion para obtener los detalles de venta con status 0
    public function getDetailsDev(){
        return $this->hasMany('App\Models\SaleDetail', 'sale_id', 'id')->where('status', 0);
    }

    // Definir la relación con Customer
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
        // return $this->belongsTo(Customer::class);
    }

    //Funcion para obtener marca (linea) del producto
    public function getPaymentMethod(){
        return $this->hasOne('App\Models\PaymentMethod', 'id', 'payment_method_id');
    }

    //Funcion para obtener la suma de todos los movimientos de la venta
    public function getAmount($id){
        $sales_detail_total = SaleDetail::where('sale_id', $id)->sum('total');
        $sale_details = SaleDetail::where('sale_id', $id)->get();
        $descuento = 0;

        if(count($sale_details)){
            foreach($sale_details as $item){
                if(count($item->getCantSalesDetail)){
                    foreach($item->getCantSalesDetail as $detal_cant){
                        $descuento += (float)$detal_cant->total_descuento;
                    }
                }
            }
        }

        $details_descuento = 0;
        $details = $sales_detail_total - $descuento;
        return $details;
    }

    //Funcion para obtener la suma de todos los movimientos de la venta en efectivo
    public function getMontoEfectivo(){
        $details = Sale::whereBetween('updated_at', [Auth::User()->getTurno->entrada, Auth::User()->getTurno->salida])->where('type_payment', 'efectivo')->sum('total'); 
        // $details = $this->hasMany('App\Models\SaleDetail', 'sale_id', 'id');
        return $details;
    }

    //funcion para asignar folio a la venta
    public function addFolio($sale_id){
        $sale = Sale::find($sale_id);
        if(is_object($sale)){
            return 'R-'.$sale->id; 
        }

        return false;
    }

    //realcion con payment methods
    public function paymentMethod()
    {
        return $this->belongsTo('App\Models\PaymentMethod');
    }

    //funcion para obtener los totales de tarjetas y efectivo
    public function getTotal($type, $date){
        $total = Sale::whereBetween('date', [$date])->where('type_payment', $type)->sum('total_sale'); 
        return $total;
    }
}
