<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Role, Permission};

class RoleController extends Controller
{
    //vista principal roles
    public function index($status)
    {
        $roles = Role::where('status', $status)->get();
        return view('Admin.roles.index', ['roles' => $roles, 'status' => $status]);
    }

    //funcion para guardar rol
    public function store(Request $request)
    {
        $validated = $request->validate([ 
            'name' => 'required',
        ]); 

        $rol = new Role();
        $rol->name = $request->name;
        $rol->description = $request->description;
        $rol->save();

        return redirect()->back()->with('success', 'Rol creado con exito.');
    }

    // funcioon para actualizar rol
    public function update(Request $request)
    {
        $validated = $request->validate([ 
            'name' => 'required',
        ]); 

        $rol = Role::find($request->id);
        if(is_object($rol)){
            $rol->name = $request->name;
            $rol->description = $request->description;
            $rol->save();
            return redirect()->back()->with('success', 'Rol actualizado con exito.');
        }

        return redirect()->back()->with('error', 'Ocurrio un error.');
    }

    //funcion para inhabilitar rol
    public function destroy(string $id)
    {
        $rol = Role::find($id);
        if(is_object($rol)){
            $rol->status = 0;
            $rol->save();
            return redirect()->back()->with('success', 'Rol inhabilitado con exito.');
        }

        return redirect()->back()->with('error', 'Ocurrio un error.');
    }

    //funcion para habilitar rol
    public function enable(string $id)
    {
        $rol = Role::find($id);
        if(is_object($rol)){
            $rol->status = 1;
            $rol->save();
            return redirect()->back()->with('success', 'Rol habilitado con exito.');
        }

        return redirect()->back()->with('error', 'Ocurrio un error.');
    }

    public function permissions(Role $role)
    {
        $permissions = Permission::orderBy('module')->orderBy('description')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('Admin.roles._permissions', [
            'role' => $role,
            'roleId' => $role->id,
            'permissions' => $permissions,
            'rolePermissions' => $role->permissions
        ]);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
        'permissions' => 'sometimes|array',
        'permissions.*' => 'sometimes|integer|exists:permissions,id'
        ]);

        try {
            $role->permissions()->sync($validated['permissions'] ?? []);
            
            return response()->json([
                'success' => true,
                'message' => 'Permisos actualizados correctamente',
                'data' => [
                    'permissions_count' => $role->permissions()->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar permisos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
