<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BranchUser extends Model
{
    use HasFactory;
    protected $table = 'branch_user';

    function getBranch(){
        return $this->hasOne('App\Models\Branch', 'id', 'branch_id');
    }
}
