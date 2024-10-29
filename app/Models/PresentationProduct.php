<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresentationProduct extends Model
{
    use HasFactory;
    protected $table = 'presentations_product';

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'description',
        'unidad_sat_id',
        'status',
    ];

    //Funcion para obtener marca (linea) del producto
    public function getUnidadSat(){
        return $this->hasOne('App\Models\UnidadSat', 'id', 'unidad_sat_id');
    }
}
