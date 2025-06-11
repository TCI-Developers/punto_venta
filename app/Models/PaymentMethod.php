<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $table = 'payment_methods';

    protected $fillable = [
        'id',
        'pay_method',
        'description',
    ];

    public function setPaymentMethods($payment_methods) {
        if(count($payment_methods)){
            foreach($payment_methods as $item){
                $payment_method = PaymentMethod::find((int)$item->{'record_id#'});
                if(!isset($payment_method)){
                    $payment_method = new PaymentMethod();
                } 
                $payment_method->id = (int)$item->{'record_id#'};
                $payment_method->pay_method = $item->c_metodopago;
                $payment_method->description = $item->{'descripción'};
                $payment_method->status = 1;
                $payment_method->save();
            }
            return true;
        }
        return false;
    }

    public function getPaymentMethod($id){
        return $this->find($id);
    }
}
