<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SaleDetail;

class Box extends Model
{
    use HasFactory;
    protected $table = 'boxes';

    //Funcion para obtener producto
    public function getTotalDevolutions($startDate, $endDate){
        $total = SaleDetail::whereBetween('updated_at', [$startDate, $endDate])->where('status', 0)->sum('total');
        return $total ?? 0;
    }
}
