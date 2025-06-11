<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
Use App\Models\BranchUser;

class Branch extends Model
{
    use HasFactory;
    protected $table = 'branchs';

      protected $fillable = [
        'razon_social',
        'name',
        'address',
        'phone',
        'rfc',
    ];

    //funcion para obtener los users de una sucursal
    public function getUsers($branch_id){
        $users = BranchUser::where('branch_id', $branch_id)->select('user_id')->get();
        if(count($users)){
            return $users;
        }
        return 'false';
    }
}
