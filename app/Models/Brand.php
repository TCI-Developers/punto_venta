<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $table = 'brands';

    protected $fillable = [
        'id',
        'name',
        'description',
    ];

    public function setBrands($brands) {
        if(count($brands)){
            foreach($brands as $item){
                $brand = Brand::find((int)$item->{'record_id#'});
                if(!isset($brand)){
                    $brand = new Brand();
                } 
                $brand->id = (int)$item->{'record_id#'};
                $brand->name = $item->linea;
                $brand->description = $item->descripcion;
                $brand->save();
            }
            return true;
        }
        return false;
    }
}
