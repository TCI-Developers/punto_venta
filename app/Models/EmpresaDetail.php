<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SaleDetail;

class EmpresaDetail extends Model
{
    use HasFactory;
    protected $table = 'empresa_details';

    public function setEmpresa($detail) {
        if(count($detail)){
            foreach($detail as $item){
                $empresa = EmpresaDetail::first();
                if(!isset($empresa)){
                    $empresa = new EmpresaDetail();
                } 
                $empresa->name = $item->nombre;
                $empresa->rfc = $item->rfc;
                $empresa->address = $item->direccion;
                $empresa->save();
            }
            return true;
        }
        return false;
    }
}
