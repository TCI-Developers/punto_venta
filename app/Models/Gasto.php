<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    use HasFactory;
    protected $table = 'gastos';

    protected $fillable = [
        'box_id',
        'branch_id',
        'user_id',
        'concepto',
        'monto',
        'description',
        'status',
    ];

    public function getBox(){
        return $this->belongsTo('App\Models\Box', 'box_id');
    }

    public function getUser(){
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
