<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;


class RolePermissionController extends Controller
{
    public function assignPermissions(Request $request, Role $role)
    {
        $permissions = $request->input('permissions', []);
        $role->permissions()->sync($permissions);
        
        return back()->with('success', 'Permisos actualizados correctamente');
    }
}
