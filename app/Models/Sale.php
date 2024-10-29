<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
Use App\Models\SaleDetail;

class Sale extends Model
{
    use HasFactory;
    protected $table = 'sales';

    //Funcion para obtener marca (linea) del producto
    public function getClient(){
        return $this->hasOne('App\Models\Customer', 'id', 'customer_id');
    }
    
    //Funcion para obtener marca (linea) del producto
    public function getDetails(){
        return $this->hasMany('App\Models\SaleDetail', 'sale_id', 'id');
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
        $details = SaleDetail::where('sale_id', $id)->sum('amount');
        // $details = $this->hasMany('App\Models\SaleDetail', 'sale_id', 'id');
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
}
