<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\Crypt;

class EmpresaDetail extends Model
{
    use HasFactory;
    protected $table = 'empresa_details';

    protected $fillable = [
        'name',
        'razon_social',
        'rfc',
        'regimen_fiscal',
        'codigo_postal',
        'address',
        'vigencia',
        'path_logo',
        'matriz_token',
    ];
    
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
                // se cifra igual que en AdminController::empresaUpdate() -- la columna siempre
                // debe contener el valor cifrado, nunca texto plano, o UserController::vigencia()
                // truena al intentar descifrarla en el siguiente login.
                $empresa->vigencia = Crypt::encrypt($item->vigencia ?? '');
                $empresa->path_logo = $item->path_logo;
                $empresa->save();
            }
            return true;
        }
        return false;
    }

    //Funcion para obtener el la sucursal asignada
    public function getBranch(){
        return $this->hasOne('App\Models\Branch', 'id', 'branch_id');
    }
}
