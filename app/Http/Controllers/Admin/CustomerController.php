<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class CustomerController extends Controller
{    
    //funcion para mostrar listado de clientes
    public function index()
    {   
        return view('admin.customers.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required'], ['name' => 'El nombre es requerido.']); 
        
        $message = 'agregado';
        $customer = new Customer();
        if(isset($request->id) && $request->id != ''){
            $customer = Customer::find($request->id);
            $message = 'actualizado';
        }
        $customer->name = $request->name;
        $customer->razon_social = $request->razon_social;
        $customer->rfc = $request->rfc;
        $customer->postal_code = $request->postal_code;
        $customer->regimen_fiscal = $request->regimen_fiscal;
        $customer->save();

        return redirect()->back()->with('success', 'Cliente '.$message.' con exito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, $status)
    {   
        $message = $status ? 'habilitado':'inhabilitado';
        $customer = Customer::find($id);
        if(is_object($customer)){
            $customer->status = $status;
            $customer->save();
            return redirect()->back()->with('success', 'Cliente '.$message.' con exito.');
        }

        return redirect()->back()->with('error', 'Ocurrio un error.');
    }

    public function destroyAll(){
        $usados = DB::table('sales')->pluck('customer_id')
            ->merge(DB::table('prices')->whereNotNull('customer_id')->pluck('customer_id'))
            ->merge(DB::table('facturas')->whereNotNull('customer_id')->pluck('customer_id'))
            ->unique()->filter()->values();

        $deleted = Customer::whereNotIn('id', $usados)->delete();
        return redirect()->back()->with('success', "{$deleted} clientes eliminados. Los que tienen ventas u otras relaciones se conservaron.");
    }
}
