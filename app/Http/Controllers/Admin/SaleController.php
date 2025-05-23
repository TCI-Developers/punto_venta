<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, Sale, SaleDetail};
use Illuminate\Support\Facades\{DB,Auth};

class SaleController extends Controller
{    
    //vista principal ventas
    public function index()
    {          
        return view('admin.sales.index', ['type' => 'index', 'id' => null]);
    }

    //funcion para crear venta
    public function create(){
        return view('admin.sales.create', ['type' => 'create', 'id' => null]);
    }

    //funcion para mostrar vista edit de venta
    public function show($id){
            return view('admin.sales.create', ['type' => 'show', 'id' => $id]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            $validated = $request->validate([ 
                'customer_id' => 'required',
                'date' => 'required',
                'payment_method_id' => 'required',
                'coin' => 'required',
            ]); 

            $sale = new Sale();
            $sale->user_id = Auth::User()->id;
            $sale->branch_id = Auth::User()->branch_id;
            $sale->folio = 0;
            $sale->customer_id = $request->customer_id;
            $sale->date = date('Y-m-d');
            $sale->payment_method_id = $request->payment_method_id;
            $sale->type_payment = $request->type_payment;
            $sale->coin = $request->coin;
            $sale->save();

            $folio = $sale->addFolio($sale->id);
            if($folio){
                $sale->folio = $folio;
                $sale->save();
            }else{
                $sale->delete();
                return redirect()->back()->with('error', 'Ocurrio un error al generar el folio.');
            }

            return redirect()->route('sale.show', $sale->id)->with('success', 'Venta creada con exito.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {   
        
        $validated = $request->validate($this->rulesSales($request->status), $this->messagesError()); 

        $sale = Sale::find($id);
        if(!is_object($sale)){
            return redirect()->back()->with('error', 'Ocurrio un error.');
        }
       
        $sale->user_id = Auth::User()->id;
        if($request->status == 'cobro'){
            $sale->amount_received = $request->amount_received ?? 0;
            $sale->total_sale = $request->total_sale ?? 0;
            $sale->change = $request->change ?? 0;
            $sale->status = 2;

            $message = 'realizada';
        }else{
            $sale->customer_id = $request->customer_id;
            $sale->payment_method_id = $request->payment_method_id;
            $sale->type_payment = $request->type_payment;
            $sale->coin = $request->coin;

            $message = 'actualizada';
        }        
        $sale->save();

        return redirect()->route('sale.index')->with('success', 'Venta '.$message.' con exito.');
        // return redirect()->back()->with('success', 'Venta '.$message.' con exito.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    //funcion para guardar un movimiento de almacen
    public function storeDetail(Request $request){
        $validated = $request->validate([ 
            'presentation_id' => 'required',
            'cant' => 'required|numeric|min:1',
            'price' => 'required',
        ], ['cant' => 'La salida debe de ser mayor a 0.']); 
       
        $sale_detail = new SaleDetail();
        $sale_detail->part_to_product_id = $request->presentation_id;
        $sale_detail->sale_id = $request->sale_id;
        $sale_detail->cant = $request->cant;
        $sale_detail->unit_price = $request->price;
        $sale_detail->iva = $request->taxes == 'IVA' ? $request->total_taxes:0;
        $sale_detail->ieps = $request->taxes == 'IE3' ? $request->total_taxes:0;
        $sale_detail->subtotal = $request->subtotal;
        $sale_detail->amount = $request->amount;
        $sale_detail->save();

        return redirect()->back()->with('success', 'Se agrego movimiento con exito.');
    }

    //funcion para actualizar un movimiento de almacen
    public function updateDetail(Request $request){
        $validated = $request->validate([ 
            'presentation_id' => 'required',
            'cant' => 'required|numeric|min:1',
            'price' => 'required',
        ], ['cant' => 'La salida debe de ser mayor a 0.']); 
       
        $sale_detail = SaleDetail::find($request->mov_sale_id);
        if(!is_object($sale_detail)){
            return redirect()->back()->with('error', 'Ocurrio un error.');
        }

        $sale_detail->part_to_product_id = $request->presentation_id;
        $sale_detail->cant = $request->cant;
        $sale_detail->unit_price = $request->price;
        $sale_detail->iva = $request->taxes == 'IVA' ? $request->total_taxes:0;
        $sale_detail->ieps = $request->taxes == 'IE3' ? $request->total_taxes:0;
        $sale_detail->subtotal = $request->subtotal;
        $sale_detail->amount = $request->amount;
        $sale_detail->save();

        return redirect()->back()->with('success', 'Se actualizo movimiento con exito.');
    }

    function rulesSales($status){
        if($status == 'cobro'){
            return ['amount_received' => 'required',
            'total_sale' => 'required',
            'change' => 'required'];
        }

        return ['customer_id' => 'required',
            'payment_method_id' => 'required',
            'coin' => 'required',
            'type_payment' => 'required'];
    }

    function messagesError(){
        return ['amount_received' => 'Monto recibido es requerido.',
                'payment_method_id' => 'Metodo de pago es requerido.',
                'coin' => 'Moneda es requerido.',
                'type_payment' => 'Tipo de pago es requerido.',];
    }
}
