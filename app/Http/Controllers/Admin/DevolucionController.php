<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Auth};
use App\Models\{Devolucion, Product, Sale, SaleDetail, SaleDetailCant, PartToProduct, Branch, Driver, EmpresaDetail, Compra, DetalleCompra, DevolucionMatriz, DevolucionMatrizDetail};
use Hamcrest\Type\IsObject;

class DevolucionController extends Controller
{
    //index principal para listado de devoluciones
    public function index($status = 1){  
        $empresa = EmpresaDetail::first();
        return view('Admin.devoluciones.index', ['status' => $status, 'branch_id' => $empresa->branch_id ?? null ]);
        // return view('Admin.devoluciones.index', ['status' => $status, 'devoluciones' => []])->with('Selecciona una sucursal.');
    }

    //funcion para mostrar el listado de compras
    public function indexCompras(){
        return view('Admin.devoluciones_matriz.index');
    }

    //funcion para mostrar el listado de compras
    public function showMatriz($id){
        return view('Admin.devoluciones_matriz.show', ['id' => $id]);
    }

    //funcion para guardar la devolucion de una compra de matriz
    public function storeMatriz(Request $request, $compra_id){
        $request->validate([ 
            'driver_id' => 'required',
            'date' => 'required',
            'cant_devoluciones' => ['required', 'array', 'min:1'],
            'cant_devoluciones.*' => ['required', 'numeric', 'gt:0']
            ],[
            'driver.required' => 'El chofer es requerido.',
            'date.required' => 'La fecha es requerida.',
            'cant_devoluciones.required' => 'Debes ingresar devoluciones.',
            'cant_devoluciones.array' => 'El formato de devoluciones no es válido.',
            'cant_devoluciones.min' => 'Debes ingresar al menos dos devoluciones.',
            'cant_devoluciones.*.required' => 'La cantidad es requerida.',
            'cant_devoluciones.*.numeric' => 'La cantidad debe ser un número.',
            'cant_devoluciones.*.gt' => 'La cantidad debe ser mayor a cero.',
        ]);

        foreach($request->cant_devoluciones ?? [] as $index => $item){
            $detail = DetalleCompra::find($index);
            if(!is_object($detail)){
                return redirect()->back()->with('error', 'Ocurrio un error inesperado #52.');
            }
        }

        try {
            $empresa = EmpresaDetail::first();
            $driver = Driver::find($request->driver_id);
            
            foreach ($request->cant_devoluciones ?? [] as $key => $item) {
                $descuento = $request->descuentos[$key] / $request->total_products[$key];
                $impuesto = $request->impuestos[$key] / $request->total_products[$key];
                $subtotal = $request->precio_unit[$key] * $item;
                
                $product = Product::where('code_product', $request->code_product)->first();
                $dev_matriz = new DevolucionMatriz();
                $dev_matriz->driver = $driver->name;
                $dev_matriz->compra_id = $compra_id;
                $dev_matriz->product_id = $product->product_id;
                $dev_matriz->branch_id = $empresa->branch_id;
                $dev_matriz->cantidad = $item;
                $dev_matriz->impuesto = $impuesto;
                $dev_matriz->total_impuesto = ($impuesto * $item);
                $dev_matriz->descuento = $descuento;
                $dev_matriz->total_descuentos = ($descuento* $item);
                $dev_matriz->subtotal = $subtotal;
                $dev_matriz->total = ($subtotal + ($impuesto* $item)) - ($descuento * $item);
                $dev_matriz->description = $request->description;
                $dev_matriz->date = $request->date;
                $dev_matriz->save();

                $product->existence = ((float)$product->existence - (float)$item);
                $product->save();

                //se debe de ver si solo se hara la devolucion por un producto o mas
                if($this->hasInternetConnection()){
                    // $this->postQuickBase('bt4rwsy8q', $item, $request, $product, $empresa->branch_id); 
                }
            }

            $compra = Compra::find($compra_id);
            $compra->status_devolucion = 1;
            $compra->save();

            return redirect()->route('devoluciones.indexCompras')->with('success', 'Devolución realizada con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
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

    //funcion para mostrar la vista de la devolucion de venta y actualizar
    public function showDevMatriz($devolucion_id){
        $branch = EmpresaDetail::first();
        $drivers = Driver::where('status', 1)->get();
        $devolution = DevolucionMatriz::find($devolucion_id);
        $detail_compra = DetalleCompra::where('compra_id', $devolution->getCompra->id)->where('producto_id', $devolution->product_id)->first();
        $compra = Compra::find($devolution->compra_id);

        if(!is_object($devolution) || !is_object($detail_compra)){
            return redirect()->back()->with('error', 'Ocurrio algo inesperado en la devolución.');
        }

        if(!is_object($devolution)){
            return redirect()->back()->with('error', 'No se pudo completar la acción.');
        }

        return view('admin.devoluciones_matriz.show_dev', ['devolution' => $devolution, 'compra' => $compra, 'branch' => $branch,
                                                             'detail_compra' => $detail_compra, 'drivers' => $drivers]);
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
        $product_existentes = Product::find($presentation->product_id);

        if($type == 'suma'){
            // $presentation->stock = $presentation->stock + $cant;
            $product_existentes->existence = $product_existentes->existence + $cant;
        }else{
            // $presentation->stock= $presentation->stock - $cant;
            $product_existentes->existence = $product_existentes->existence - $cant;
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
        $product_existentes->save();
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
    function postQuickBase($table_id, $cantidad, $request, $product, $branch_id){
        $url = "https://api.quickbase.com/v1/records";
        $userToken = env('USER_TOKEN');
        
        $fields = [52 => ["value" => true], //checkbox de sucursal
                    53 => ["value" => 11225], //record_id producto  
                    57 => ["value" => $branch_id], //record_id de sucursal
                    8 => ["value" => $request->description], //descripcion de la devolucion
                    22 => ["value" => $request->driver_id], //record_id del chofer
                    10 => ["value" => $cantidad] // cantidad que se esta devolviendo
                ];
     
        $headers = [
            "QB-Realm-Hostname: ".env('DOMINIO').".quickbase.com",
            "User-Agent: {User-Agent}",
            "Authorization: QB-USER-TOKEN $userToken",
            "Content-Type: application/json"
        ];

        $data = [
            "to" => $table_id,
            "data" => [
                $fields
            ]
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);          
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);
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

}
