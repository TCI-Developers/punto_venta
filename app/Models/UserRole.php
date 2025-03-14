<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;
    protected $table = 'role_user';

    //funcion para obtener rol
    public function getRol(){
        return $this->hasOne('App\Models\Role', 'id', 'role_id');
    }
}
