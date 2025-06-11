<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Branch, EmpresaDetail};
use Illuminate\Support\Facades\{DB, Auth};

class AdminController extends Controller
{
    //vista inicio
    // public function index()
    // {
    //     return view('Admin.branchs.index', ['status' => 1]);
    // }

    //funcion para ingresar el monto inicial de la caja
    public function startAmountBox(){
        $empresa = EmpresaDetail::first();
        $ban = true;
        foreach(Auth::User()->getBranchs ?? [] as $item){
            if($item->branch_id === $empresa->branch_id){
                $ban = false;
                break;
            }
        }

        if($ban){
            Auth::logout();
            return redirect()->back()->with('error', 'No perteneces a esta sucursal.');
        }

        return view('Admin.box.start_amount');
    }

    //vista datos de la empresa
    public function empresa()
    {
        $empresa = EmpresaDetail::first();
        $branchs = Branch::get();
        return view('Admin.empresa.show', ['empresa' => $empresa, 'branchs' => $branchs]);
    }

    //funcion para actualizar los datos de la empresa
    public function empresaUpdate(Request $request)
    {   
        try {
            if(!Auth::User()->hasAnyRole(['root', 'admin'])){
                return redirect()->back()->with('error', 'La acción no se pudo completar.');
            }

            $empresa = EmpresaDetail::first();
            $empresa->razon_social = strtoupper($request->razon_social);
            $empresa->name = strtoupper($request->name);
            $empresa->rfc = strtoupper($request->rfc);
            $empresa->address = $request->address;
            $empresa->branch_id = $request->branch_id;
            $empresa->save();

            return redirect()->back()->with('success', 'La acción se completo con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo completar.');
        }
    }
}
