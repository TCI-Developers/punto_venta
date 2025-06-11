<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PartToProduct;

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

    //Funcion para obtener marca (linea) del producto
    // public function getPartToProduct($presentatio_id, $code_bar){
    //     $part_to_product = PartToProduct::where('presentation_product_id', $presentatio_id)
    //                                     ->where('code_bar', $code_bar)->first();
    //     if(is_object($part_to_product)){
    //         return $part_to_product;
    //     }
    //     return null;
    // }
}
