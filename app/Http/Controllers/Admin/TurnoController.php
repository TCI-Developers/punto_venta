<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Turno;

class TurnoController extends Controller
{
    //vista principal turnos
    public function index(string $status)
    {
        $turnos = Turno::where('status', $status)->get();
        return view('Admin.turnos.index', ['turnos' => $turnos, 'status' => $status]);
    }

    //funcion para guardar turno
    public function store(Request $request)
    {
        $validated = $request->validate([ 
            'turno' => 'required',
            'entrada' => 'required',
            'salida' => 'required',
        ]); 

        $turno = new Turno();
        $turno->turno = $request->turno;
        $turno->description = $request->description;
        $turno->entrada = $request->entrada;
        $turno->salida = $request->salida;
        $turno->save();

        return redirect()->back()->with('success', 'Turno creado con exito.');
    }

    //funcion para actualizar turno
    public function update(Request $request)
    {
        $validated = $request->validate([ 
            'turno' => 'required',
            'entrada' => 'required',
            'salida' => 'required',
        ]); 

        $turno = Turno::find($request->id);
        if(is_object($turno)){
            $turno->turno = $request->turno;
            $turno->description = $request->description;
            $turno->entrada = $request->entrada;
            $turno->salida = $request->salida;
            $turno->save();
            return redirect()->back()->with('success', 'Turno actualizado con exito.');
        }

        return redirect()->back()->with('error', 'Ocurrio un error.');
    }

    //funcion para inhabilitar un turno
    public function destroy(string $id)
    {
        $turno = Turno::find($id);
        if(is_object($turno)){
            $turno->status = 0;
            $turno->save();
            return redirect()->back()->with('success', 'Turno inhabilitado con exito.');
        }

        return redirect()->back()->with('error', 'Ocurrio un error.');
    }

    //funcion para habilitar turnos
    public function enable(string $id)
    {
        $turno = Turno::find($id);
        if(is_object($turno)){
            $turno->status = 1;
            $turno->save();
            return redirect()->back()->with('success', 'Turno habilitado con exito.');
        }

        return redirect()->back()->with('error', 'Ocurrio un error.');
    }
}
