<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proveedor;

class ProveedorController extends Controller
{
    //listado de proveedores
    public function index($status = 1)
    {   
        $proveedores = Proveedor::where('status', $status)->orderBy('name', 'asc')->get();
        return view('Admin.proveedores.index', ['proveedores' => $proveedores, 'status' => $status]);
    }

    //funcion para mostrar vista para crear o actuproveedor
    public function create($proveedor_id = null){
        if(!is_null($proveedor_id)){
            $proveedor = Proveedor::find($proveedor_id);
            if(is_object($proveedor)){
                return view('Admin.proveedores.create', ['proveedor' => $proveedor]);
            }
        }

        return view('Admin.proveedores.create');
    }

    //funcion para guardar proveedor
    public function store(Request $request, $proveedor_id = null){
        $this->rules($request);
        try {
            if(!is_null($proveedor_id)){
                $proveedor = Proveedor::find($proveedor_id);
                $message = 'actualizado';
            }else{
                $proveedor = new Proveedor();
                $message = 'guardado';
            }

            $proveedor->name = $request->name;
            $proveedor->code_proveedor = $request->code_proveedor;
            $proveedor->rfc = $request->rfc;
            $proveedor->phone = $request->phone;
            $proveedor->contacto = $request->contacto;
            $proveedor->email = $request->email;
            $proveedor->address = $request->address;
            $proveedor->credit_days = $request->credit_days ?? 0;
            $proveedor->credit = $request->credit ?? 0;
            $proveedor->saldo = $request->saldo ?? 0;
            $proveedor->save();

            return redirect()->route('proveedor.index')->with('success', 'Proveedor '.$message.' con exito.');
        }catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo ejecutar, recarga e intentalo de nuevo.');
        }
    }

    //funcion para inhabilitar o habilitar
    public function enable($proveedor_id, $status){
        try {
            $proveedor = Proveedor::find($proveedor_id);
            $proveedor->status = $status == 1 ? 0:1;
            $proveedor->save();

            $message = $status = 1 ? 'inhabilitado':'habilitado';

            return redirect()->back()->with('success', 'Proveedor '.$message.' con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo ejecutar, recarga e intentalo de nuevo.');
        }
    }

    //funcion para validar los campos requeridos 
    function rules($request){
        $validated = $request->validate([ 
            'name' => 'required',
        ],[
            'name.required' => 'El nombre es requerido.'
        ]); 
    }
    
}
