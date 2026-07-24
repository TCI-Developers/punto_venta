<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Gasto, Box, EmpresaDetail};
use Illuminate\Support\Facades\Auth;

class GastoController extends Controller
{
    //vista principal: gastos del turno abierto del usuario autenticado
    public function index(){
        $box = Box::where('user_id', Auth::User()->id)->where('status', 0)->orderBy('id', 'desc')->first();
        $gastos = is_object($box)
            ? Gasto::where('box_id', $box->id)->where('status', 1)->orderBy('id', 'desc')->get()
            : collect();

        return view('Admin.gastos.index', [
            'box' => $box,
            'gastos' => $gastos,
            'total_gastos' => $gastos->sum('monto'),
        ]);
    }

    //funcion para registrar un gasto durante el turno abierto
    public function store(Request $request){
        $request->validate([
            'concepto' => 'required|max:255',
            'monto'    => 'required|numeric|min:0.01',
        ], [
            'concepto.required' => 'El concepto es requerido.',
            'monto.required'    => 'El monto es requerido.',
            'monto.numeric'     => 'El monto debe ser numérico.',
            'monto.min'         => 'El monto debe ser mayor a 0.',
        ]);

        $box = Box::where('user_id', Auth::User()->id)->where('status', 0)->orderBy('id', 'desc')->first();
        if(!is_object($box)){
            return redirect()->back()->with('error', 'No tienes un turno abierto.');
        }

        $empresa = EmpresaDetail::first();

        $gasto = new Gasto();
        $gasto->box_id = $box->id;
        $gasto->branch_id = $empresa->branch_id ?? null;
        $gasto->user_id = Auth::User()->id;
        $gasto->concepto = $request->concepto;
        $gasto->monto = round((float)$request->monto, 2);
        $gasto->description = $request->description;
        $gasto->status = 1;
        $gasto->save();

        if($this->hasInternetConnection()){
            $this->saveGastoDBExt($gasto);
        }

        return redirect()->back()->with('success', 'Gasto registrado con éxito.')->with('gasto_ticket', $gasto->id);
    }

    //funcion para eliminar (logico) un gasto, solo mientras el turno siga abierto
    public function destroy($id){
        $gasto = Gasto::find($id);
        if(!is_object($gasto)){
            return redirect()->back()->with('error', 'Gasto no encontrado.');
        }

        $box = $gasto->getBox;
        if(!is_object($box) || $box->status != 0){
            return redirect()->back()->with('error', 'Solo puedes eliminar gastos del turno abierto.');
        }

        $gasto->status = 0;
        $gasto->save();

        if($this->hasInternetConnection()){
            $this->saveGastoDBExt($gasto);
        }

        return redirect()->back()->with('success', 'Gasto eliminado con éxito.');
    }
}
