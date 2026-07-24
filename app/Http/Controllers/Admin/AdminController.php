<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Branch, EmpresaDetail, Box, User, BranchUser};
use Illuminate\Support\Facades\{Auth, Crypt};
use Carbon\Carbon;

class AdminController extends Controller
{
    //funcion para ingresar el monto inicial de la caja
    public function startAmountBox(){
        $empresa = EmpresaDetail::first();

        $tieneAcceso = function() use ($empresa){
            foreach(BranchUser::where('user_id', Auth::User()->id)->get() as $item){
                if($item->branch_id === $empresa->branch_id){
                    return true;
                }
            }
            return false;
        };

        $ban = !$tieneAcceso();

        // si no tiene acceso local, revisamos en el momento si Matriz ya se lo dio, en vez de
        // esperar a que alguien corra la sincronizacion completa del catalogo.
        if($ban){
            $this->syncUserBranchFromMatriz(Auth::User());
            $ban = !$tieneAcceso();
        }

        if($ban){
            Auth::logout();
            return redirect()->back()->with('error', 'No perteneces a esta sucursal.');
        }

        // Validación de doble turno
        $turnoAbierto = Box::where('user_id', Auth::User()->id)->where('status', 0)->first();
        if ($turnoAbierto) {
            $esDeHoy = Carbon::parse($turnoAbierto->start_date)->isToday();
            if ($esDeHoy) {
                return redirect()->route('sale.index')->with('info', 'Ya tienes un turno abierto.');
            }
            return redirect()->route('box.turnOff')->with('warning',
                'Tienes un turno abierto desde ' .
                Carbon::parse($turnoAbierto->start_date)->format('d/m/Y H:i') .
                '. Ciérralo antes de continuar.'
            );
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

        // el token nunca se manda de vuelta al HTML (evita exponer el secreto en el codigo fuente
        // de la pagina); solo le decimos a la vista si ya hay uno guardado o no.
        $hasMatrizToken = $empresa && !empty($empresa->matriz_token);

        return view('Admin.empresa.show', ['empresa' => $empresa, 'branchs' => $branchs, 'vigencia' => $vigencia, 'hasMatrizToken' => $hasMatrizToken]);
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

            if(Auth::User()->hasRole('root') && $request->filled('matriz_token')){
                $empresa->matriz_token = Crypt::encrypt(trim($request->matriz_token));
            }

            $empresa->save();

            return redirect()->back()->with('success', 'La acción se completo con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo completar.');
        }
    }
}
