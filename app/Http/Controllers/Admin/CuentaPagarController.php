<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{CuentaPagar, CuentaPagarDetail, EmpresaDetail};
use Illuminate\Support\Facades\{DB,Auth};

class CuentaPagarController extends Controller
{
    //index vista principal
    public function index($status = null)
    {    
        $branch_id = EmpresaDetail::first()->branch_id;
        if(is_null($status)){
            $cuentas = CuentaPagar::where('status', '!=', 0)->where('branch_id', $branch_id)->orderBy('fecha_vencimiento', 'desc')->get();
        }else{
            $cuentas = CuentaPagar::where('status', $status)->where('branch_id', $branch_id)->orderBy('fecha_vencimiento', 'desc')->get();
        }
        return view('Admin.cuentas_pagar.index', ['cuentas' => $cuentas, 'status' => $status ?? 1]);
    }

    //funcion para mostrar vista de pago
    public function show(string $id)
    {   
        $cuenta = CuentaPagar::find($id);
        $total_detail = CuentaPagarDetail::where('cxp_id', $id)->sum('importe');

        $total_debe = $cuenta->total - ($total_detail ?? 0);
        $status = $total_debe <= 0 ? true:false;

        return view('Admin.cuentas_pagar.show', ['cuenta' => $cuenta, 'total_debe' => $total_debe, 'status' => $status]);
    }

    // funcion para guardar los detalles
    public function store($cxp_id, Request $request)
    {
        $request->validate(
            ['date' => 'required', 'importe' => 'required'],
            ['date.required' => 'El campo fecha es requerido.', 'importe.required' => 'El importe es requerido.']
        );

        try {
            $old_importe = 0;
            if(isset($request->cxp_detail_id) && $request->cxp_detail_id){
                if(!Auth::User()->hasPermissionThroughModule('cuentas_por_pagar','punto_venta', 'update')){
                    return redirect()->back()->with('error', 'Acción no autorizada.');
                }

                $cuenta = CuentaPagarDetail::find($request->cxp_detail_id);
                $old_importe = $cuenta->importe;
            }else{
                $cuenta = new CuentaPagarDetail();
                $cuenta->cxp_id = $cxp_id;
            }
            $cuenta->date = $request->date;
            $cuenta->importe = (float)$request->importe;
            
            $cuenta_pagar_detail = CuentaPagarDetail::where('cxp_id', $cxp_id)->sum('importe');
            $cuenta_pagar = CuentaPagar::find($cxp_id);

            $total = $cuenta_pagar->total - (($cuenta_pagar_detail - $old_importe) + (float)$request->importe);
            $cuenta_pagar->status = $total <= 0 ? 2:1;
            $cuenta->save();
            $cuenta_pagar->save();

            return redirect()->back()->with('success', 'La acción se completo con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo completar.');
        }

    }

    //funcion para eliminar detalle
    public function destroy($id){
        try {
            $cuenta_detail = CuentaPagarDetail::find($id);
            $total_details = CuentaPagarDetail::where('cxp_id', $cuenta_detail->cxp_id)->sum('importe');
            $cuenta_detail->delete();

            $cuenta = CuentaPagar::find($cuenta_detail->cxp_id);
            $cuenta->status = ($cuenta->total - $total_details) <= 0 ? 2:1;
            $cuenta->save();


            return redirect()->back()->with('success', 'La acción se completo con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo completar.');    
        }
    }
}
