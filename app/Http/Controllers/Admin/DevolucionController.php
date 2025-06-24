<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Auth};
use App\Models\{Devolucion, Product, Sale, SaleDetail, SaleDetailCant, PartToProduct, Branch, Driver, EmpresaDetail};

class DevolucionController extends Controller
{

    // verificar en cordes de caja las devoluciones
    /**
     * Display a listing of the resource.
     */
    public function index($status = 1){  
        $empresa = EmpresaDetail::first();
        if(!is_null($empresa->branch_id)){
            $devoluciones = Devolucion::where('status', $status)->where('branch_id', $empresa->branch_id)->orderBy('id', 'desc')->get();
            return view('Admin.devoluciones.index', ['status' => $status, 'devoluciones' => $devoluciones]);
        }
        return view('Admin.devoluciones.index', ['status' => $status, 'devoluciones' => []])->with('Selecciona una sucursal.');
    }

    //funcion para mostrar las devoluciones uqe se hicieron durante las fechas de un corte
    public function indexDevCorte($startDate, $endDate){
        $sale_details = SaleDetail::whereBetween('created_at', [$startDate, $endDate])->where('status', 0)
                        ->select('sale_id') // Solo selecciona sale_id
                        ->distinct()->get();

        $devoluciones = [];
        if(count($sale_details)){
            foreach($sale_details as $item){
                if(count($item->getDevoluciones)){
                    foreach($item->getDevoluciones as $dev){
                        $devoluciones[] = $dev;
                    }
                }
            }
        }

        return view('Admin.devoluciones.index', ['status' => 1, 'devoluciones' => $devoluciones]);
    }

    //funcion para mostrar listado de ventas de una semana
    public function showListadoVentas(){  
        $empresa = EmpresaDetail::first();
        $branch_id = $empresa->branch_id; 
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime($endDate."- 1 week"));
        $sales = Sale::where('branch_id', $branch_id)->whereBetween('date', [$startDate, $endDate])->orderBy('folio', 'desc')->get();

        $productos = Product::get();
        return view('Admin.devoluciones.lista_ventas', ['productos' => $productos, 'sales' => $sales]);
    }

    //funcion para mostrar vista de crear devolucion de venta
    public function createSaleToDevolucion($sale_id){
        $sale = Sale::find($sale_id);
        $sale_details = $sale->getDetails;

        return view('Admin.devoluciones.create_devolucion', ['sale' => $sale, 'sale_details' => $sale_details]);
    }

    //funcion para mostrar la vista de la devolucion de venta y actualizar
    public function showDevSale($devolucion_id){
        $devolution = Devolucion::find($devolucion_id);
        if(!is_object($devolution)){
            return redirect()->back()->with('error', 'Ocurrio algo inesperado en la devolución.');
        }

        $sale = Sale::find($devolution->sale_id);
        if(!is_object($devolution)){
            return redirect()->back()->with('error', 'No se pudo completar la acción.');
        }
        $sale_details = $sale->getDetails;
        $sale_details_dev = $sale->getDetailsDev;

        return view('admin.devoluciones.create_devolucion', ['devolution' => $devolution, 'sale' => $sale,
                                                             'sale_details' => $sale_details, 'sale_details_dev' => $sale_details_dev]);
    }

    //funcion para eliminar un detalle de venta con estatus 0
    public function deleteDetailDev($devolution_id, $detail_dev_id){
        $sale_detail_dev = SaleDetail::find($detail_dev_id);

        // PONER AQUI LA CANTIDAD Y TOTALES DE DEVOLUCIONES PARA RESTAR
        $devolution = Devolucion::find($devolution_id);
        if(!is_object($devolution)){
            return redirect()->back()->with('error', 'La no se pudo completar la acción.');
        }

        if(!is_object($sale_detail_dev)){
            return redirect()->back()->with('error', 'No se pudo completar la acción.');
        }

        $cantidad = $devolution->cantidad;
        $descuentos = $devolution->total_descuento;
        $total_devolucion = $devolution->total_devolucion;

        if(count($sale_detail_dev->getCantSalesDetailDev)){
            $arr = $sale_detail_dev->getCantSalesDetailDev; 
            foreach($arr as $detail){
                $aux = SaleDetailCant::where('sale_detail_id', $detail->sale_detail_id)
                                        ->where('part_to_product_id', $detail->part_to_product_id)
                                        ->where('status', 0)
                                        ->first();

                $sale_detail = $aux->getSaleDetail;

                $cantidad -= $aux->cant;
                $descuentos -= $aux->tota_descuento;
                $total_devolucion -= $sale_detail->total;

                $this->setStock($aux, $aux->cant, 'resta');

                is_object($aux) ? $aux->delete():'';
            }
        }
        // dd($devolution, count($sale_detail_dev->getCantSalesDetailDev), $cantidad);
        $sale_detail_dev->delete();

        $devolution->cantidad = $cantidad;
        $devolution->total_descuentos = $descuentos;
        $devolution->total_devolucion = $total_devolucion;
        $devolution->save();

        return redirect()->back()->with('success', 'Se elimino con exito.');
    }

    //funcion para mostrar vista de crear o actualizar devolucion
    public function showDevolucion($devolucion_id){
        $devolucion = Devolucion::find($devolucion_id);
        $productos = Product::get();
        return view('Admin.devoluciones.create', ['devolucion' => $devolucion, 'productos' => $productos]);
    }

    //funcion para guardar una devolucion de venta
    public function store(Request $request, $devolucion_id = null){ 
        if(isset($request->part_to_product_id) && count($request->part_to_product_id)){
            $cantidad = 0;
            $total_descuentos = 0;
            $total_devolucion = 0;

            if(!is_null($devolucion_id)){
                $devolucion = Devolucion::find($devolucion_id);
                $cantidad = $devolucion->cantidad;
                $total_descuentos = $devolucion->total_descuentos;
                $total_devolucion = $devolucion->total_devolucion;
            }

            for ($i=0; $i < count($request->part_to_product_id) ; $i++) { 
                $sale = Sale::find($request->sale_id);

                $sale_detail_dev = new SaleDetail();
                $sale_detail_dev->sale_id = $request->sale_id;
                $sale_detail_dev->part_to_product_id = $request->part_to_product_id[$i];
                $sale_detail_dev->amount = $request->subtotal[$i];
                $sale_detail_dev->subtotal = $request->subtotal[$i];
                $sale_detail_dev->iva = $request->iva[$i];
                $sale_detail_dev->ieps = $request->ieps[$i];
                $sale_detail_dev->unit_price = $request->unit_price[$i];
                $sale_detail_dev->total = $request->total[$i];
                $sale_detail_dev->status = 0;
                $sale_detail_dev->created_at = $sale->updated_at ?? '';
                $sale_detail_dev->save();
                
                $sale_detail_cant_dev = new SaleDetailCant();
                $sale_detail_cant_dev->sale_detail_id = $sale_detail_dev->id;
                $sale_detail_cant_dev->sale_id = $request->sale_id;
                $sale_detail_cant_dev->part_to_product_id = $sale_detail_dev->part_to_product_id;
                $sale_detail_cant_dev->cant = $request->cant[$i];
                $sale_detail_cant_dev->descuento = $request->descuento[$i];
                $sale_detail_cant_dev->total_descuento = $request->total_descuento[$i];
                $sale_detail_cant_dev->status = 0;
                $sale_detail_cant_dev->save();

                $cantidad += $request->cant[$i];

                $total_descuentos += $request->total_descuento[$i];
                $total_devolucion += $request->total[$i];

                $this->setStock($sale_detail_cant_dev, $request->cant[$i], 'suma');
            }

            // validar los campos, no vacios
            if(is_null($devolucion_id)){
                $empresa = EmpresaDetail::first();
                $devolucion = new Devolucion();
                $devolucion->sale_id = $request->sale_id; //campo para devolucion de alaguna venta
                $devolucion->branch_id = $empresa->branch_id; //campo para devolucion de alaguna venta
            }
            $devolucion->cantidad = $cantidad;
            $devolucion->description = $request->notes;
            $devolucion->fecha_devolucion = $request->fecha_devolucion;
            $devolucion->user_dev = Auth::User()->id; //usuario que realizo la devolucion
            $devolucion->total_descuentos = $total_descuentos; //total descuentos
            $devolucion->total_devolucion = $total_devolucion; //total devolucion sin aplicar los descuentos que tenian los productos
            $devolucion->save();

            if($this->hasInternetConnection()){
                $ctrl = new \App\Http\Controllers\Controller();
                $ctrl->saveDevolutionDBExt($devolucion, $devolucion_id ? true:false);
            }

            return redirect()->route('devoluciones.showDevSale', $devolucion->id)->with('ticket', 'ok');
        }

        return redirect()->route('devoluciones.index')->with('error', 'No se pudo completar la acción.');
    }

    //funcion para sumar stock del producto y vigencia del producto
    function setStock($sale_detail_cant, $cant, $type){
        $presentation = PartToProduct::find($sale_detail_cant->part_to_product_id);
        if($type == 'suma'){
            $presentation->stock = $presentation->stock + $cant;
        }else{
            $presentation->stock= $presentation->stock - $cant;
        }

        if($presentation->tipo_descuento != null && $presentation->vigencia_cantidad_fecha == 'cantidad' && $sale_detail_cant->descuento > 0){
            if($type == 'suma'){
                $presentation->vigencia = (float)$presentation->vigencia + $cant;
            }else{
                $presentation->vigencia = (float)$presentation->vigencia - $cant;
            }
        }

        if($presentation->vigencia < 0){
            $presentation->vigencia = 0;
        }

        $presentation->save();
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
        $url = "https://".env('DOMINIO').".quickbase.com/db/".$data['table_id']; //url a donde se consulta
    
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

    //funcion para mostrar vista devolucion a matriz
    public function createMatriz(){
        $empresa = EmpresaDetail::first();
        $branch_id = $empresa->branch_id;
        $branch = Branch::find($branch_id);
        $drivers = Driver::where('status', 1)->get();
        $products = Product::get();

        return view('Admin.devoluciones_matriz.create', [
            'branch' => $branch,'drivers' => $drivers,'products' => $products,
        ]);
    }

    //funcion para guardar la devolucion a matriz
    public function storeMatriz(Request $request){
        $validated = $request->validate([ 
            'product_id' => 'required',
            'cant' => 'required',
            ],[
            'product_id' => 'El producto es requerido.',
            'cant' => 'La cantidad es requerida.',
        ]);

        $product = Product::find($request->product_id);
        $presentacion = $product->getPartToProduct;
        
        if(!is_null($presentacion)){
            $validated = $request->validate([ 
                'cant' => 'required|lte:'.($presentacion->stock).''
                ],[
                'cant' => 'La cantidad es mayor al stock actual del producto. Cantidad actual '.$presentacion->stock.'.',
            ]);
        }
        dd($presentacion);
        // dd($request);

    }
}
