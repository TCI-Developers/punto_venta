<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, File, Process};
use App\Models\{Compra, DetalleCompra, DetalleCompraEntrada, Proveedor, Product, CuentaPagar, EmpresaDetail};
use Barryvdh\DomPDF\Facade\{PDF};

class CompraController extends Controller
{
    //listado de proveedores
    public function index($status = 1)
    {      
        // $response = $this->traspasosMatriz();
        $empresa = EmpresaDetail::first();
        $branch_id = $empresa->branch_id;
        $compras = Compra::where('branch_id', 8)->get();
        // $compras = Compra::where('branch_id', $branch_id)->get();

        // if($response){
        //     return view('Admin.compras.index', ['compras' => $compras, 
        //                 'status' => $status, 'info' => 'Tienes compras por importar, pero no tienes actualizados tus productos.']);
        // }

        return view('Admin.compras.index', ['compras' => $compras, 'status' => $status]);
    }

    // //funcion para mostrar vista para crear o actuproveedor
    public function create($compra_id = null){
        $user = Auth::User();
        return view('Admin.compras.create', ['compra_id' => $compra_id, 'user' => $user]);
    }

    //funcion para guardar proveedor
    public function store(Request $request, $compra_id = null){
        $this->rules($request, is_null($compra_id) ? 'store':'update');

        try {
            if(!is_null($compra_id)){
                $compra = Compra::find($compra_id);
                $message = 'actualizada';
            }else{
                $compra = new Compra();
                $message = 'generada';
            }
            $user = Auth::User();
            $empresa = EmpresaDetail::first();
            
            if(is_null($compra_id)){
                $compra->folio = 0;
            }

            $compra->branch_id = $empresa->branch_id;
            $compra->proveedor_id = $request->proveedor_id;
            $compra->user_id = $user->id;
            $compra->programacion_entrega = $request->programacion_entrega;
            $compra->plazo = $request->plazo;
            $compra->moneda = $request->moneda;
            $compra->tipo = $request->tipo;
            $compra->fecha_vencimiento = $request->fecha_vencimiento;
            $compra->observaciones = $request->observaciones;
            $compra->save();

            if(is_null($compra_id)){ //si no existe el id de la compra, crea el folio
                $compra->folio = $compra->addFolio($compra->id);
            }

            $impuestos = 0;
            $subtotal = 0;
            $total = 0;
            
            foreach($request->product_id ?? [] as $index => $item){
                $product = Product::find($item);
                $detalle_compra = $this->detalleCompra($compra, $product, $request, $index);

                $this->newEntrada($request->entrada[$index], $detalle_compra, $user->id);

                $impuestos += $this->formatNumberr($request->impuestos[$index]);
                $subtotal += $this->formatNumberr($request->subtotal[$index]);
                $total += $this->formatNumberr($request->total[$index]);
            }

            foreach($request->entrada_saved ?? [] as $detalle_compra_id => $item){
                $detalle = DetalleCompra::find($detalle_compra_id);
                $detalle->impuestos = $this->formatNumberr($request->impuestos_saved[$detalle_compra_id]);
                $detalle->subtotal = $this->formatNumberr($request->subtotal_saved[$detalle_compra_id]);
                $detalle->total = $this->formatNumberr($request->total_saved[$detalle_compra_id]);
                $detalle->save();

                $this->newEntrada($item, $detalle, $user->id);

                if(!is_null($compra_id)){
                    $impuestos += $this->formatNumberr($request->impuestos_saved[$detalle_compra_id]);
                    $subtotal += $this->formatNumberr($request->subtotal_saved[$detalle_compra_id]);
                    $total += $this->formatNumberr($request->total_saved[$detalle_compra_id]);
                }
            }

            $compra->impuesto_productos = $impuestos;
            $compra->subtotal = $subtotal;
            $compra->total = $total;
            $compra->save();

            $this->saveCompraDBExterna($compra, $compra_id ? true:false);

            return redirect()->route('compra.show', $compra->id)->with('success', 'Compra '.$message.' con exito.');
        }catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo ejecutar, recarga e intentalo de nuevo.');
        }
    }

    //funcion para guardar el cieere de la compra
    public function storeRecibido(Request $request, $compra_id){  
        $request->validate(
            ['recibido' => 'required'],['recibido.required' => 'El campo recibido es requerido.',]
        );

        try {
            $compra = Compra::find($compra_id);

            foreach($request->recibido ?? [] as $index => $item){
                $detalle_compra_entrada = DetalleCompraEntrada::find($index);
                $detalle_compra_entrada->recibido = $item;
                $detalle_compra_entrada->save();

                
                $detalle_compra = DetalleCompra::find($detalle_compra_entrada->detalle_compra_id);
                $subtotal = $detalle_compra->precio_unitario * $item;
                $impuestos = $subtotal * $detalle_compra->amount_taxes;

                $detalle_compra->subtotal = $subtotal;
                $detalle_compra->impuestos = $impuestos;
                $detalle_compra->total = $impuestos + $subtotal;
                if($compra->tipo == 'OC'){
                    $product = Product::find($detalle_compra->producto_id);
                    if($product->getPartToProduct){
                        $presentacion = $product->getPartToProduct;
                        $presentacion->stock = $presentacion->stock + $item;
                        $presentacion->save();
                    }

                    if($product->getPartToProductDespiezado){
                        $despiezado = $product->getPartToProductDespiezado;
                        $despiezado->stock = $despiezado->stock + $item;
                        $despiezado->save();
                    }

                    $product->existence = ((int)$presentacion->stock) + $item;
                    // $product->existence = ((int)$product->existence ?? 0) + $item;
                    $product->save();
                }
                $detalle_compra->save();
            }

            $compra->status = 5;

            $impuestos = 0;
            $subtotal = 0;
            $total = 0;
            foreach($request->subtotal_saved ?? [] as $detalle_compra_id => $item){
                $impuestos += $this->formatNumberr($request->impuestos_saved[$detalle_compra_id]);
                $subtotal += $this->formatNumberr($request->subtotal_saved[$detalle_compra_id]);
                $total += $this->formatNumberr($request->total_saved[$detalle_compra_id]);
            }

            $compra->impuesto_productos = $impuestos;
            $compra->subtotal = $subtotal;
            $compra->total = $total;
            $compra->save();

            $empresa = EmpresaDetail::first();
            $cxp = new CuentaPagar();
            $cxp->newCXP($compra, $empresa->branch_id); 

            return redirect()->back()->with('success', 'Compra cerrada con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo completar.');
        }
    }

    //funcion para cambiar el status de la compra
    public function status($compra_id, $status){
        try {
            $compra = Compra::find($compra_id);
            if($status == 4){
                $compra->fecha_recibido = date('Y-m-d');
                $compra->fecha_vencimiento =  date('Y-m-d', strtotime("+$compra->plazo days", strtotime($compra->fecha_recibido)));
            }
            if($status == 2 && !Auth::User()->hasPermissionThroughModule('compras','punto_venta','auth')){
                return redirect()->back()->with('error', 'Acción no autorizada');
            }

            $compra->status = $status;
            $compra->save();
            $message = 'autorizada';

            $this->saveCompraDBExterna($compra, true);

            return redirect()->back()->with('success', 'Compra '.$message.' con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La acción no se pudo completar.');
        }

    }

    //funcion para guardar el detalle de compra
    function detalleCompra($compra, $product, $request, $index){
        $detalle_compra = new DetalleCompra();
        $detalle_compra->compra_id = $compra->id;
        $detalle_compra->producto_id = $product->id;
        $detalle_compra->descripcion_producto = $product->description;
        $detalle_compra->precio_unitario = $product->precio;
        $detalle_compra->precio_mayoreo = $product->precio_mayoreo;
        $detalle_compra->taxes = $product->taxes;
        $detalle_compra->amount_taxes = $product->amount_taxes;
        $detalle_compra->subtotal = $this->formatNumberr($request->subtotal[$index]);
        $detalle_compra->impuestos = $this->formatNumberr($request->impuestos[$index]);
        $detalle_compra->total = $this->formatNumberr($request->total[$index]);
        $detalle_compra->save();

        return $detalle_compra;
    }

    //funcion para agregar nueva entrada de regisro que ya existe
    function newEntrada($entrada, $detalle_compra, $user_id){
        $detalle_compra_entrada = new DetalleCompraEntrada();
        $detalle_compra_entrada->storeEntrada($entrada, $detalle_compra, $user_id);
    }

    //funcion para eliminar producto
    function destroy($detalle_id){
        $detalle = DetalleCompra::find($detalle_id);
        $detalle->status = 0;
        $detalle->save();

        $compra = Compra::find($detalle->compra_id);
        $subtotal = 0;
        $impuestos = 0;
        $total = 0;
        foreach($compra->getDetalles ?? [] as $item){
            $subtotal += $item->subtotal; 
            $impuestos += $item->impuestos; 
            $total += $item->total; 
        }
        $compra->subtotal = $subtotal;
        $compra->impuesto_productos = $impuestos;
        $compra->total = $total;
        $compra->save();

        return redirect()->back()->with('success', 'Producto '. $detalle->descripcion_producto .' eliminado con exito.');
    }

    //funcion para mostrar pdf
    public function pdf($compra_id){
        $empresa = EmpresaDetail::first();
        $compra = Compra::find($compra_id);

        if (!$compra || !$empresa) {
            return 'Datos no encontrados.';
        }

        $logoPath = public_path('img/logo_cliente.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = PDF::loadView('Admin.compras.pdf', [
            'compra' => $compra,
            'empresa' => $empresa, 
            'logoBase64' => $logoBase64
        ])->setOptions(['isRemoteEnabled' => true]);

        return $pdf->stream('compra_'.$compra_id.'.pdf');
    }

    //funcion para validar los campos requeridos 
    function rules($request, $type){
        $rule = $type == 'update' ? ['proveedor_id' => 'required','moneda' => 'required','tipo' => 'required',]:
            ['proveedor_id' => 'required','moneda' => 'required','tipo' => 'required','product_id' => 'required'];

        $messages = $type == 'update' ? [
            'proveedor_id.required' => 'El proveedor es requerido.',
            'moneda.required' => 'La moneda es requerida.',
            'tipo.required' => 'El tipo de compra es requerida.',
        ]:[
            'proveedor_id.required' => 'El proveedor es requerido.',
            'moneda.required' => 'La moneda es requerida.',
            'tipo.required' => 'El tipo de compra es requerida.',
            'product_id.required' => 'Selecciona como minimo un prodcuto con su respectiva entrada.'
        ];

        $validated = $request->validate($rule,$messages); 
    }   
    
    private function saveCompraDbExterna($compra, $update){
        if($this->hasInternetConnection()){
            $ctrl = new \App\Http\Controllers\Controller();
            $ctrl->saveCompraDBExt($compra, $update);
        }
    }

    //consultamos y guardamos los nuevos registros en local
    private function traspasosMatriz(){
        $startDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $endDate = date('Y-m-d 23:59:59', strtotime('+1 days'));

        $compras_matriz = $this->consultDb('compras', ['tipo' => 'T',
                'created_at' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);

        if($compras_matriz->status == 'success'){
            $ban = 0;
            $productos = [];
            foreach($compras_matriz->data ?? [] as $item){
                $productos = json_decode($item->details_json);
                foreach ($productos as $value) {
                    $product = Product::where('code_product', $value->productID)->first();
                    if(!is_object($product)){
                        dd($value);
                        echo '*'.$value->productID.'<br>';
                        $ban=1;
                        // break;
                    }
                }
            }
        dd('ok');
            if($ban == 1){
                return true;
                die;
            }

            foreach($compras_matriz->data ?? [] as $item){
                $compra = Compra::where('folio', $item->folio)->first();
                if(!is_object($compra)){
                    $compra = new Compra();
                    $compra->folio = $item->folio;
                    $compra->branch_id = $item->branch_id;
                    $compra->proveedor_id = 1;//quitar porque seran nullables
                    $compra->user_id = 1; //quitar porque seran nullables
                    $compra->user = $item->user;
                    $compra->programacion_entrega = $item->programacion_entrega;
                    $compra->plazo = $item->plazo;
                    $compra->moneda = $item->moneda;
                    $compra->impuesto_productos = $item->impuesto_productos;
                    $compra->descuentos = $item->descuentos;
                    $compra->subtotal = $item->subtotal;
                    $compra->total = $item->total;
                    $compra->tipo = $item->tipo;
                    $compra->observaciones = $item->observaciones;
                    $compra->status = $item->status;
                    $compra->created_at = $item->created_at;
                    $compra->save();

                    $productos = json_decode($item->details_json);
                    foreach ($productos as $value) {
                        $product = Product::where('code_product', $value->productID)->first();
                        $detalle = new DetalleCompra();
                        $detalle->producto_id = $product->id;
                        $detalle->taxes = $product->taxes ?? 0;
                        $detalle->amount_taxes = $product->amount_taxes ?? 0;
                        $detalle->compra_id = $compra->id;
                        $detalle->descripcion_producto = $value->productID;
                        $detalle->precio_unitario = $value->precioUnitario;
                        $detalle->subtotal = $value->subTotal;
                        $detalle->total = $value->total;
                        $detalle->save();
                        
                        $detalle_compra_entrada = new DetalleCompraEntrada();
                        $detalle_compra_entrada->compra_id = $compra->id;
                        $detalle_compra_entrada->detalle_compra_id = $detalle->id;
                        $detalle_compra_entrada->entrada = $value->salida;
                        $detalle_compra_entrada->user_id = 1;
                        $detalle_compra_entrada->save();

                    }

                }
            }
        }
    }
}
