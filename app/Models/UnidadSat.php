<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadSat extends Model
{
    use HasFactory;
    protected $table = 'unidades_sat';

    protected $fillable = [
        'id',
        'clave_unidad',
        'name',
        'description',
    ];

    //funcion para guardar todos las unidades del SAT
    public function setUnidades($unidades) {
        if(count($unidades)){
            foreach($unidades as $item){
                $unidad = UnidadSat::find((int)$item->{'record_id#'});
                if(!isset($unidad)){
                    $unidad = new UnidadSat();
                    $unidad->id = (int)$item->{'record_id#'};
                }                
                $unidad->clave_unidad = $item->c_claveunidad;
                $unidad->name = $item->nombre;
                $unidad->description = $item->{'descripción'};
                $unidad->save();
            }
            return true;
        }
        return false;
    }
}
