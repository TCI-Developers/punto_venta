<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores';

    protected $fillable = [
        'id',
        'name',
        'code_proveedor',
        'rfc',
        'phone',
        'contacto',
        'email',
        'address',
        'credit_days',
        'credit',
        'saldo',
    ];

    public function setProveedores($proveedores) {

        if(count($proveedores)){
            foreach($proveedores as $item){
                $proveedor = Proveedor::find((int)$item->record_id_);
                if(!isset($proveedor)){
                    $proveedor = new Proveedor();
                } 
                
                $proveedor->id = (int)$item->record_id_;
                $proveedor->name = (string)$item->nombre;
                $proveedor->code_proveedor = (string)$item->codigo_proveedor;
                $proveedor->rfc = (string)$item->__rfc;
                $proveedor->phone = (string)$item->tel;
                $proveedor->contacto = (string)$item->contacto;
                $proveedor->email = (string)$item->e_mail;
                $proveedor->address = (string)$item->direccion;
                $proveedor->credit_days = (float)$item->dias_credito;
                $proveedor->credit = (float)$item->credito;
                $proveedor->saldo = (float)$item->saldo;
                $proveedor->save();
            }
            return true;
        }
        return false;
    }
}
