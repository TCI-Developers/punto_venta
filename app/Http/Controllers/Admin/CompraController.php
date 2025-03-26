<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Compra, Proveedor};

class CompraController extends Controller
{
    //listado de proveedores
    public function index($status = 1)
    {   
        $compras = Compra::where('status', $status)->get();
        return view('Admin.compras.index', ['compras' => $compras, 'status' => $status]);
    }

    // //funcion para mostrar vista para crear o actuproveedor
    public function create($compra_id = null){
        $user = Auth::User();
        return view('Admin.compras.create', ['compra_id' => $compra_id, 'user' => $user]);
    }

    // //funcion para guardar proveedor
    public function store(Request $request, $compra_id = null){
        $this->rules($request);

        try {
            if(!is_null($compra_id)){
                $compra = Compra::find($compra_id);
                $message = 'actualizada';
            }else{
                $compra = new Compra();
                $message = 'generada';
            }

            $user = Auth::User();

            $compra->folio = 0;
            $compra->branch_id = $user->branch_id;
            $compra->proveedor_id = $request->proveedor_id;
            $compra->user_id = $user->id;
            $compra->programacion_entrega = $request->programacion_entrega;
            $compra->plazo = $request->plazo;
            $compra->moneda = $request->moneda;
            $compra->tipo = $request->tipo;
            $compra->fecha_vencimiento = $request->fecha_vencimiento;
            $compra->observaciones = $request->observaciones;
            $compra->save();

            $compra->folio = $compra->addFolio($compra->id);
            $compra->save();

            return redirect()->route('compra.show', $compra->id)->with('success', 'Compra '.$message.' con exito.');
        }catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo ejecutar, recarga e intentalo de nuevo.');
        }
    }

    // //funcion para inhabilitar o habilitar
    // public function enable($proveedor_id, $status){
    //     try {
    //         $proveedor = Proveedor::find($proveedor_id);
    //         $proveedor->status = $status == 1 ? 0:1;
    //         $proveedor->save();

    //         $message = $status = 1 ? 'inhabilitado':'habilitado';

    //         return redirect()->back()->with('success', 'Proveedor '.$message.' con exito.');
    //     } catch (\Throwable $th) {
    //         return redirect()->back()->with('error', 'La acción no se pudo ejecutar, recarga e intentalo de nuevo.');
    //     }
    // }

    //funcion para validar los campos requeridos 
    function rules($request){
        $validated = $request->validate([ 
            'proveedor_id' => 'required',
            'moneda' => 'required',
            'tipo' => 'required',
        ],[
            'proveedor_id.required' => 'El proveedor es requerido.',
            'moneda.required' => 'La moneda es requerida.',
            'tipo.required' => 'El tipo de compra es requerida.',
        ]); 
    }
    
}
