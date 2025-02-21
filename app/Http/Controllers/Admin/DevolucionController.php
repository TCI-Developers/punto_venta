<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Devolucion, Product, Sale, PartToProduct};

class DevolucionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($status = 1)
    {   
        $devoluciones = Devolucion::where('status', $status)->get();
        return view('Admin.devoluciones.index', ['status' => $status, 'devoluciones' => $devoluciones]);
    }

    //funcion para mostrar vista de crear o actualizar devolucion
    public function show($devolucion_id = null)
    {   
        if($devolucion_id == 'sale'){
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime($endDate."- 1 week"));
            $sales = Sale::whereBetween('date', [$startDate, $endDate])->get();
        }else if($devolucion_id){
            $devolucion = Devolucion::find($devolucion_id);
        }

        $productos = Product::get();
        return view('Admin.devoluciones.create', ['devolucion' => $devolucion ?? null, 'productos' => $productos, 'sales' => $sales ?? []]);
    }

    //funcion para mostrar la vista de crear devolcuion de una venta
    public function createDevolucionSale($devolucion_id = null, $sale_id){
        if($sale_id){
            $sale = Sale::find($sale_id);
            $devolucion = Devolucion::find($devolucion_id);
            if(is_object($sale)){
                $sale_details = $sale->getDetails;
                $productos = [];
                if(count($sale_details)){
                    foreach($sale_details as $index => $item){
                        $part_to_product = PartToProduct::find($item->part_to_product_id);
                        $sale_details_cants = $item->getCantSalesDetail;
                        
                        if(is_countable($sale_details_cants)){
                            $cant = 0;
                            foreach($sale_details_cants as $details_cant){
                                if($part_to_product->id == $details_cant->part_to_product_id){
                                    $cant += $details_cant->cant;
                                }
                            }
                        }

                        $productos[$index]['id'] = $part_to_product->getProduct->id;
                        $productos[$index]['product'] = $part_to_product->getProduct->code_product.' - '.$part_to_product->getProduct->description;
                        $productos[$index]['cantidad'] = $cant ?? 0;
                        $productos[$index]['part_product_id'] = $item->part_to_product_id;
                        $productos[$index]['product_presentation'] = $item->getPartToProduct->getPresentation->type;
                    }
                }

                return view('Admin.devoluciones.create', ['devolucion' => $devolucion ?? null, 'productos_sale' => $productos, 'sale' => $sale]);
            }

            return redirect()->back()->with('error', 'Ocurrio un error inesperado.');
        }

        return redirect()->back()->with('error', 'Ocurrio un error inesperado.');
    }

    //funcion para guardar una devolucion
    public function store(Request $request)
    {
        // validar los campos, no vacios
        $devolucion = new Devolucion();
        $devolucion->product_id = $request->product_id;
        $devolucion->part_to_product_id = $request->part_to_product_id ?? null; //campo para devolucion de alaguna venta
        $devolucion->sale_id = $request->sale_id ?? null; //campo para devolucion de alaguna venta
        $devolucion->cantidad = $request->cantidad;
        $devolucion->description = $request->description;
        $devolucion->fecha_devolucion = $request->fecha_devolucion;
        $devolucion->save();

        // $this->saveDBExterna($request); //Guardado en DB externa

        //logica para mandar guardar a QuickBase
        $data['table_id'] = "bqa4qy3sd";
        $data['usertoken'] = "b8degy_fwjc_0_djjg8pab6ss873bfjuhnjb6vdbut";
        $data['apptoken'] = "dkxavxndzybjwqi43f52dsyakvp"; 
        $data['dominio'] = "aortizdemontellanoarevalo.quickbase.com";

        // $this->postQuickBase($data, $request);

        return redirect()->route('devoluciones.index')->with('success', 'Devolucion registrada con exito.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // validar los campos, no vacios
        $devolucion = Devolucion::find($id);
        if(is_object($devolucion)){
            $devolucion->product_id = $request->product_id;
            $devolucion->cantidad = $request->cantidad;
            $devolucion->description = $request->description;
            $devolucion->fecha_devolucion = $request->fecha_devolucion;
            $devolucion->save();

            return redirect()->route('devoluciones.index')->with('success', 'Devolución actualizada con exito.');
        }
        return redirect()->route('devoluciones.index')->with('error', 'Ocurrio un error.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, $status)
    {
        $devolucion = Devolucion::find($id);
        if(is_object($devolucion)){
            $devolucion->status = $status;
            $devolucion->save();

            $message = $status == 0 ? 'inhabilito':'habilito';

            return redirect()->route('devoluciones.index')->with('success', 'Se '.$message.' con exito la devolución');
        }
        return redirect()->back()->with('success', 'Ocurrio un error.');
    }

    //funcion para mandar informacion a DB externa
    function saveDBExterna($request){
        // Guardar en la base de datos de Hostinger
        DB::connection('db_externa')->table('devoluciones')->insert([
            'product_id' => $request->product_id,
            'part_to_product_id' => $request->part_to_product_id ?? null,
            'sale_id' => $request->sale_id ?? null,
            'cantidad' => $request->cantidad,
            'description' => $request->description,
            'fecha_devolucion' => $request->fecha_devolucion,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    //funcion para 
    function postQuickBase($data, $request){
        $url = "https://aortizdemontellanoarevalo.quickbase.com/db/".$data['table_id']; //url a donde se consulta
    
        $body = "<qdbapi>
            <usertoken>".$data['usertoken']."</usertoken>
            <apptoken>".$data['apptoken']."</apptoken>
                <field fid='52'>true</field>
                <field fid='53'>$request->product_id</field>
                <field fid='8'>$request->desription</field>
                <field fid='10'>$request->cantidad</field>
        </qdbapi>";


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
        curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml',
            'Content-Length:',
            'QUICKBASE-ACTION: API_AddRecord'
        ));
        
        $response = curl_exec($ch);
        curl_close ($ch);
       dd($response);
    }
}
