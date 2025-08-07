<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product, Sale, SaleDetail, PaymentMethod, EmpresaDetail, PartToProduct};
use Illuminate\Support\Facades\{DB,Auth};

class SaleController extends Controller
{    
    //vista principal ventas
    public function index()
    {          
        try {
            // $this->updatePricesProducts();
            return view('admin.sales.index', ['type' => 'index', 'id' => null]);
        } catch (\Throwable $th) {
            return view('admin.sales.index', ['type' => 'index', 'id' => null]);
        }
    }

    //funcion para crear venta
    public function create(){
        $empresa = EmpresaDetail::first();
        try {
            $user = Auth::User();
            $payment_method = PaymentMethod::where('pay_method', 'PUE')->first();
            $sale = new Sale();
            $sale->user_id = $user->id;
            $sale->branch_id = $empresa->branch_id;
            $sale->folio = 0;
            $sale->customer_id = 1;
            $sale->date = date('Y-m-d');
            $sale->payment_method_id = $payment_method->id;
            $sale->type_payment = 'efectivo';
            $sale->coin = 'MXN';
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
        } catch (\Throwable $th) {
            if(is_null($empresa->branch_id)){
                return redirect()->back()->with('error', 'Asigna una sucursal a tus datos de empresa.');
            }
            return redirect()->back()->with('error', 'Ocurrio un error inesperado.');
        }
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
            // $validated = $request->validate([ 
                // 'customer_id' => 'required',
                // 'date' => 'required',
                // 'payment_method_id' => 'required',
                // 'coin' => 'required',
            // ]); 
            
            $empresa = EmpresaDetail::first();
            $user = Auth::User();
            $payment_method = PaymentMethod::find($request->payment_method_id);
            if(!is_object($payment_method)){
                return redirect()->back()->with('error', 'Ocurrio un error inesperado.');
            }
            $sale = new Sale();
            $sale->user_id = $user->id;
            $sale->branch_id = $empresa->branch_id;
            $sale->folio = 0;
            $sale->customer_id = $request->customer_id;
            $sale->date = date('Y-m-d');
            $sale->payment_method_id = $payment_method->id;
            $sale->type_payment = $payment_method->pay_method == 'PPD' ? 'tarjeta':'efectivo';
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
            $sale->status = 2;
            $message = 'realizada';
            $sale->save();
            return redirect()->route('sale.index')->with('success', 'Venta '.$message.' con exito.');
        }else{
            $payment_method = PaymentMethod::find($request->payment_method_id);

            $sale->customer_id = $request->customer_id;
            $sale->payment_method_id = $payment_method->id;
            // $sale->type_payment = $payment_method->pay_method == 'PPD' ? 'tarjeta':'efectivo';
            $sale->type_payment = $request->type_payment;
            $sale->coin = $request->coin;

            $message = 'actualizada';
            $sale->save();
            return redirect()->back()->with('success', 'Venta '.$message.' con exito.');
        }        
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $sale = Sale::find($id);
            if($sale->getDetails){
                $sale->status = 0;
                $sale->save();
                return redirect()->back()->with('success', 'Venta eliminada con exito.');
            }else{
                return redirect()->back()->with('info', 'Esta venta no puede ser eliminada, contiene productos.');
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'No se puedo completar la acción.');
        }
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
            'coin' => 'required'];
    }

    function messagesError(){
        return ['amount_received' => 'Monto recibido es requerido.',
                'payment_method_id' => 'Metodo de pago es requerido.',
                'coin' => 'Moneda es requerido.',
                'type_payment' => 'Tipo de pago es requerido.',];
    }

    //actualizamos los precios de productos en dado caso de que existan nuevos
    function updatePricesProducts(){
        $startDate = date('Y-m-d H:i:s', strtotime('-7 days'));
        $endDate = date('Y-m-d').' 23:00:00';
        $data = [
            'updated_at' => [
                'start' => $startDate,
                'end' => $endDate, 
            ],
        ];
        $db = $this->consultDb('products', $data);

        if(isset($db->status)){
            foreach ($db->data ?? [] as $value) {
                $product = Product::find($value->id);
                dd($value);
                if(is_object($product)){
                    $product->precio = $value->precio;
                    $product->precio_mayoreo = $value->precio_mayoreo;
                    $product->precio_despiece = $value->precio_despiece;
                    $product->updated_at = date('Y-m-d H:i:s');
                    $product->save();

                    if(count($product->getPartToProducts)){ //presentaciones del producto
                        foreach($product->getPartToProducts as $item){
                            $presentacion = PartToProduct::find($item->id);
                            if(is_object($presentacion)){
                                $presentacion->price = $value->precio;
                                $presentacion->price_mayoreo = $value->precio_mayoreo;
                                $presentacion->save();
                            }
                        }
                    }

                    if(count($product->getPagetPartToProductDespiezadostToProducts)){ //despieces del producto
                        foreach($product->getPartToProductDespiezados as $item){
                            $presentacion = PartToProduct::find($item->id);
                            if(is_object($presentacion)){
                                $presentacion->price = ($value->precio_despiece / $presentacion->cantidad_despiezado);
                                $presentacion->price_mayoreo = $value->precio_mayoreo;
                                $presentacion->save();
                            }
                        }
                    }
                }
            }
        }
    }
}
