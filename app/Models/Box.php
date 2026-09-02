<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Box extends Model
{
    use HasFactory;
    protected $table = 'boxes';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTotalDevolutions($startDate, $endDate){
        $saleIds = Sale::where('user_id', $this->user_id)->pluck('id');
        $total = Devolucion::whereIn('sale_id', $saleIds)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('total_devolucion');
        return $total ?? 0;
    }
}
