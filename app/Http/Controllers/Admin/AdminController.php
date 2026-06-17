<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Branch, EmpresaDetail, Box, User};
use Illuminate\Support\Facades\{Auth, Crypt};

class AdminController extends Controller
{
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

        // Validación de doble turno: si ya tiene un turno abierto, mandarlo directo a ventas
        $turnoAbierto = Box::where('user_id', Auth::User()->id)->where('status', 0)->exists();
        if($turnoAbierto){
            return redirect()->route('sale.index')->with('info', 'Ya tienes un turno abierto.');
        }

        // Obtener el último cierre para mostrar el monto de referencia
        $ultimoCierre = Box::where('status', '>', 0)->orderBy('end_date', 'desc')->first();
        $ultimoCierreUser = $ultimoCierre ? User::find($ultimoCierre->user_id) : null;

        return view('Admin.box.start_amount', [
            'ultimoCierre'     => $ultimoCierre,
            'ultimoCierreUser' => $ultimoCierreUser,
        ]);
    }

    //vista datos de la empresa
    public function empresa()
    {
        $empresa = EmpresaDetail::first();
        $branchs = Branch::where('status', 1)->get();

        $vigencia = null;
        if($empresa && $empresa->vigencia){
            try { $vigencia = Crypt::decrypt($empresa->vigencia); } catch(\Throwable $e) {}
        }

        return view('Admin.empresa.show', ['empresa' => $empresa, 'branchs' => $branchs, 'vigencia' => $vigencia]);
    }

    //funcion para actualizar los datos de la empresa
    public function empresaUpdate(Request $request)
    {   
        try {
            if(!Auth::User()->hasAnyRole(['root', 'admin'])){
                return redirect()->back()->with('error', 'La acción no se pudo completar.');
            }

            $request->validate([
                'name'    => 'required|max:255',
                'rfc'     => 'required|max:13',
                'address' => 'required',
            ], [
                'name.required'    => 'El nombre es requerido.',
                'rfc.required'     => 'El RFC es requerido.',
                'address.required' => 'La dirección es requerida.',
            ]);

            $empresa = EmpresaDetail::first();
            if(!is_object($empresa)){
                $empresa = new EmpresaDetail();
            }
            $empresa->razon_social    = strtoupper($request->razon_social);
            $empresa->name            = strtoupper($request->name);
            $empresa->rfc             = strtoupper($request->rfc);
            $empresa->regimen_fiscal  = $request->regimen_fiscal ? trim($request->regimen_fiscal) : null;
            $empresa->codigo_postal   = $request->codigo_postal   ? trim($request->codigo_postal)  : null;
            $empresa->address         = $request->address;
            $empresa->branch_id       = $request->branch_id;

            if(Auth::User()->hasRole('root') && $request->filled('vigencia')){
                $empresa->vigencia = Crypt::encrypt($request->vigencia);
            }

            $empresa->save();

            return redirect()->back()->with('success', 'La acción se completo con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo completar.');
        }
    }
}
