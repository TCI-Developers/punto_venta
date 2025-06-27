<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SaleDetail;

class PermissionRole extends Model
{
    use HasFactory;
    protected $table = 'permission_role';
}
