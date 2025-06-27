<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Permission, Module};

class PermissionController extends Controller
{
    // listado de permisos
    public function index()
    {   
        $permissions = Permission::orderBy('module')->orderBy('description')->get();
        $modules = Module::where('status', 1)->get();
        return view('Admin.permissions.index',  ['permissions' => $permissions, 'modules' => $modules]);
    }

    // funcion para guardar el permiso
    public function store(Request $request)
    {
        $permission = Permission::where('module', $request->module)->where('action', $request->action)->first();
        if(is_object($permission)){
            return redirect()->back()->with('error', 'El permiso ya existe');
        }

        $permission = new Permission();
        $permission->module = $request->module;
        $permission->submodule = $request->submodule;
        $permission->action = $request->action;
        $permission->description = $request->description;
        $permission->save();

        return redirect()->back()->with('success', 'Permiso creado con exito');
    }

    // funcion para actualizar el permiso
    public function update(Request $request)
    {
        try {
        $permission = Permission::find($request->id);
        $permission->module = $request->module;
        $permission->submodule = $request->submodule;
        $permission->action = $request->action;
        $permission->description = $request->description;
        $permission->save();

        return redirect()->back()->with('success', 'Permiso actualizado con exito');
        } catch (\Throwable $th) {
           return redirect()->back()->with('error', 'No se pudo completar la acción.');
        }
    }

    // funcion para eliminar el permiso
    public function destroy(string $id)
    {
        try {
            
            Permission::find($id)->delete();

        return redirect()->back()->with('success', 'Permiso eliminado con exito');
        } catch (\Throwable $th) {
           return redirect()->back()->with('error', 'No se pudo completar la acción.');
        }
    }
}
