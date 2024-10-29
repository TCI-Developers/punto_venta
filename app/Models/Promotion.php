<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;
    protected $table='promotions'; 

    //Funcion para obtener marca (linea) del producto
    public function getBranch(){
        return $this->hasOne('App\Models\Branch', 'id', 'branch_id');
    }
}
