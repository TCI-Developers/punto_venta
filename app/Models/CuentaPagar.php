<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaPagar extends Model
{
    use HasFactory;
    protected $table = 'cuentas_pagar';

    //funcion para generar cuenta por pagar
    function newCXP($compra, $branch_id){
        $cxp = new CuentaPagar();
        $cxp->compra_id = $compra->id;
        $cxp->branch_id = $branch_id;
        $cxp->fecha_vencimiento = $compra->fecha_vencimiento;
        $cxp->subtotal = $compra->subtotal;
        $cxp->impuestos = $compra->impuesto_productos;
        $cxp->total = $compra->total;
        $cxp->save();
    }

    //Funcion para obtener el proveedor
    public function getCompra(){
        return $this->hasOne('App\Models\Compra', 'id', 'compra_id');
    }

    //Funcion para obtener los detalles de cxp
    public function getDetails(){
        return $this->hasMany('App\Models\CuentaPagarDetail', 'cxp_id', 'id');
    }
}
