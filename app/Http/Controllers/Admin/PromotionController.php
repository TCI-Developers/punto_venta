<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Branch, Promotion};

class PromotionController extends Controller
{
    // index
    public function index($status = 1)
    {   
        $promotions = Promotion::where('status', $status)->get();
        return view('admin.promotions.index', ['promotions' => $promotions, 'status' => $status]);
    }

    //funcion para mostrar la vista de crear promocion
    public function create($promotion_id = null){
        if($promotion_id){
            $promotion = Promotion::find($promotion_id);
        }
        $branchs = Branch::where('status', 1)->get();
        if(!count($branchs)){
            $branchs = [];
        }
        return view('admin.promotions.create', ['branchs' => $branchs, 'promotion' => $promotion ?? null]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //hace falta validar algun campo vacio
        $promotion = new Promotion();
        $promotion->branch_id = $request->branch_id;
        $promotion->description = $request->description;
        $promotion->cantidad_producto = $request->cantidad_producto;
        $promotion->cantidad_productos_a_pagar = $request->cantidad_productos_a_pagar;
        $promotion->vigencia_cantidad = $request->vigencia_cantidad;
        $promotion->vigencia_fecha = $request->vigencia_fecha;
        $promotion->save();

        return redirect()->route('promos.index')->with('success', 'Promoción creada con exito.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //hace falta validar algun campo vacio
        $promotion = Promotion::find($id);
        $promotion->branch_id = $request->branch_id;
        $promotion->description = $request->description;
        $promotion->cantidad_producto = $request->cantidad_producto;
        $promotion->cantidad_productos_a_pagar = $request->cantidad_productos_a_pagar;
        $promotion->vigencia_cantidad = $request->vigencia_cantidad;
        $promotion->vigencia_fecha = $request->vigencia_fecha;
        $promotion->save();

        return redirect()->route('promos.index')->with('success', 'Promoción actualizada con exito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, $status)
    {
        $promotion = Promotion::find($id);
        if(is_object($promotion)){
            $promotion->status = $status;
            $promotion->save();

            $message = $status == 0 ? 'inhabilitada':'habilitada';

            return redirect()->route('promos.index')->with('success', 'Promoción '.$message.' con exito.');
        }
    }
}
