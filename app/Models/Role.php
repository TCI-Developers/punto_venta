<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = 'roles';

     protected $fillable = [
        'name',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany('App\User')->withTimesTamps();
    }

    public function permissions()
    {
        return $this->belongsToMany('App\Models\Permission');
    }

    public function permissionsRole()
    {
        return $this->belongsToMany('App\Models\PermissionRole');
    }
}
