<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{CuentaPagar};

class CuentaCobrarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($status = null)
    {    
        if(is_null($status)){
            $cuentas = CuentaPagar::where('status', '!=', 0)->orderBy('fecha_vencimiento', 'desc')->get();
        }else{
            $cuentas = CuentaPagar::where('status', $status)->orderBy('fecha_vencimiento', 'desc')->get();
        }
        return view('Admin.cuentas_pagar.index', ['cuentas' => $cuentas, 'status' => $status ?? 1]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
