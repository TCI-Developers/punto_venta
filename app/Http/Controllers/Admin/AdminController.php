<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmpresaDetail;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    //vista inicio
    public function index()
    {
        return view('dashboard');
    }

    //funcion para ingresar el monto inicial de la caja
    public function startAmountBox(){
        return view('Admin.box.start_amount');
    }

    //vista datos de la empresa
    public function empresa()
    {
        $empresa = EmpresaDetail::first();
        return view('Admin.empresa.show', ['empresa' => $empresa]);
    }

    //funcion para actualizar los datos de la empresa
    public function empresaUpdate(Request $request)
    {
        try {
            if(!Auth::User()->hasAnyRole('root', 'admin')){
                return redirect()->back()->with('error', 'La acción no se pudo completar.');
            }

            $empresa = EmpresaDetail::first();
            $empresa->razon_social = strtoupper($request->razon_social);
            $empresa->name = strtoupper($request->name);
            $empresa->rfc = strtoupper($request->rfc);
            $empresa->address = $request->address;
            $empresa->save();

            return redirect()->back()->with('success', 'La acción se completo con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo completar.');
        }
    }
}
