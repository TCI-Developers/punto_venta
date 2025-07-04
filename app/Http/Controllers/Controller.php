<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\{DB,Auth, Http, Storage, Log, Artisan};
use App\Models\{Product, Brand, Sale, PaymentMethod, UnidadSat, Driver, Proveedor, EmpresaDetail, User, Box, Devolucion, Compra};
use Barryvdh\DomPDF\Facade\PDF;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // public function __construct(){

    //     $this->middleware(function ($request, $next) {
        //         $controller = class_basename($request->route()->getController());
        //         $controllers_excluidos = ['AdminController','BranchController', 'UserController', 'RoleController', 'Controller', 'RootController'];
        //         $ban = 0;
        //         foreach($controllers_excluidos as $item){
        //             if($item == $controller){
        //                 $ban = 1;
        //                 break;
        //             }
        //         }

        //         if(!$ban && $this->sucursalUser() === false){
        //             return redirect()->route('branchs.index')->with('error', 'Selecciona una sucursal para poder acceder al sistema.');
        //         }
                
        //         return $next($request);
        //     });
    // }

    //funcion para obtener data de quickbase
    function getQuickBase($table_name_db, $data = null){
        $db = $this->validacionTabla($table_name_db, $data)['db'];
        $query = $this->validacionTabla($table_name_db, $data)['query'];
        $clist = $this->validacionTabla($table_name_db, $data)['clist'];

        $userToken = env('USER_TOKEN');
        $sortOrder = [["fieldId" => 3,"order" => "ASC"],];

        $url = "https://api.quickbase.com/v1/records/query";
     
        $headers = [
            "QB-Realm-Hostname: ".env('DOMINIO').".quickbase.com",
            "User-Agent: {User-Agent}",
            "Authorization: QB-USER-TOKEN $userToken",
            "Content-Type: application/json"
        ];
     
        $data = [
            "from" => $db,
            "select" => $clist,
            "where" => $query,
            "sortBy" => $sortOrder,
            "options" => [
                "skip" => 0,
                "top" => 0,
                "compareWithAppLocalTime" => false
            ]
        ];
     
        $ch = curl_init($url);
     
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);          
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        curl_close($ch);
        $record = json_decode($response, true);
        $fieldMap = [];
        if (isset($record['fields'])) {
            foreach ($record['fields'] as $field) {
                $label = strtolower(str_replace([' ', '-'], '_', $field['label'])); // limpieza básica
                $fieldMap[$field['id']] = $label;
            }
        }

        $values = [];

        if (isset($record['data'])) {
            foreach ($record['data'] as $dataItem) {
                $formattedItem = [];
                foreach ($dataItem as $id => $valueItem) {
                    if (isset($fieldMap[$id])) {
                        $formattedItem[$fieldMap[$id]] = $valueItem['value'];
                    }
                }
                $values[] = (object) $formattedItem; // Lo convertimos a objeto, como antes
            }
        }
        return $values;
    }

    //funcion para obtener los parametros que ocupara la consulta de quickbase
    function validacionTabla($table_name_db, $data = null){
        if($table_name_db == 'branchs'){
            // $data['db'] = 'bqbrd7fy7';
            // $data['clist'] = [15,17,16,13,6];
            $data['db'] = 'bqa4qy37m';
            $data['query'] = "{73.EX.'yes'}";
            $data['clist'] = [25,6,8,9,11,12,19];
        }else if($table_name_db == 'usuarios'){
            $data['db'] = 'brnx9pgfy';
            $data['query'] = "{30.EX.'".$data['tel']."'} AND {8.EX.'".$data['pass']."'}";
            $data['clist'] = [28,6,30,8];
        }else if($table_name_db == 'users'){
            $data['db'] = 'brnx9pgfy';
            $data['query'] = '';
            $data['clist'] = [28,6,30,8];
        }else if($table_name_db == 'brands'){
            $data['db'] = 'brer52xt3';
            $data['query'] = '';
            $data['clist'] = [3,6,7];
        }else if($table_name_db == 'payment_methods'){
            $data['db'] = 'bqgubmjca';
            $data['query'] = '';
            $data['clist'] = [3,6,7];
        }else if($table_name_db == 'unidades_sat'){
            $data['db'] = 'bqgt9zstu';
            $data['query'] = '';
            $data['clist'] = [3,6,7,8];
        }else if($table_name_db == 'products'){
            $data['db'] = 'bqa4qy4jd';
            $data['query'] = '{86.EX.0}AND{82.EX.0}';
            $data['clist'] = [3,13,29,154,43,92,86,49,155,64,65,66,67,44,79,60];
        }else if($table_name_db == 'drivers'){
            $data['db'] = 'bqa4qy3yt';
            $data['query'] = '{53.EX.0}AND{127.EX.8}';
            $data['clist'] = [3,10];
        }else if($table_name_db == 'proveedores'){
            $data['db'] = 'bqa4qy387';
            $data['query'] = '';
            $data['clist'] = [3,17,6,8,33,19,18,28,29,20,30];
        }else if($table_name_db == 'empresa_details'){
            $data['db'] = 'bqa4qy3xm';
            $data['query'] = '';
            $data['clist'] = [6,12,14];
        }
       
        return $data;
    }

    //funcion para saber si el usuario tiene sucursal asignada para entrar a mas modulos
    public function sucursalUser(){
            $user = Auth::user();
            return $user && $user->branch_id ? true : false;
    }

    //funcion para obtener los choferes
    function getDrivers($branch_id = null){
        $driver_exist = $this->existDataDb('drivers');

        if(isset($driver_exist->status) && $driver_exist->status){
            return redirect()->back()->with('info','Ya existen registros en la DB.');
        }

        $response = $this->getQuickBase('drivers');

        foreach($response ?? [] as $item){
            $data[]['name'] = $item->nombre;
        }

        foreach($data ?? [] as $item){
            $this->saveDb('drivers', $item);
        }

        $driver = new Driver();
        $driver2 = $driver->setDrivers($response);

        dd($driver_exist->status);
        // $driver_exist = is_null($branch_id) ? Driver::first():null;
        // if(!is_object($driver_exist)){
        //     $response = $this->getQuickBase('drivers');
        //     $driver = new Driver();
        //     $driver2 = $driver->setDrivers($response);

        //     if(!is_null($branch_id)){
        //         return redirect()->back()->with('success', 'Importación de choferes con exito.');
        //     }
        // }
    }

    //funcion para obtener datos de empresa
    function getEmpresa($branch_id = null){
        $empresa_exist = EmpresaDetail::first();
        if(!is_object($empresa_exist)){
            $response = $this->getQuickBase('empresa_details');
            $empresa = new EmpresaDetail();
            $empresa2 = $empresa->setEmpresa($response);
        }

        if(!is_null($branch_id)){
            return redirect()->back()->with('success', 'Importación de productos y marcas con exito.');
        }
    }

    //funcion para obtener las marcas (linea productos)
    function getBrands($branch_id = null){
        $brand_exist = is_null($branch_id) ? Brand::first():null;
        if(!is_object($brand_exist)){
            $response = $this->getQuickBase('brands');
            $brand = new Brand();
            $brand2 = $brand->setBrands($response);
        }

        $this->getProducts($branch_id);

        if(!is_null($branch_id)){
            return redirect()->back()->with('success', 'Importación de productos y marcas con exito.');
        }
    }

    //funcion para obtener todos los proudctos
    function getProducts($branch_id = null){
        $product_exist = is_null($branch_id) ? Product::first():null;

        if(!is_object($product_exist)){
            $clist = '3.13.29.154.43.92.86.49.155.64.65.66.67.44.79.60';
            $response = $this->getQuickBase('productos');
            $product = new Product();
            $product2 = $product->setProducs($response, $branch_id);
        }
    }

    //funcion para obtener las metodos de pago
    function getPaymentMethods($branch_id = null){
        $payment_method_exist = is_null($branch_id) ? PaymentMethod::first():null;
        if(!is_object($payment_method_exist)){
            $response = $this->getQuickBase('payment_methods');
        
            $payment_method = new PaymentMethod();
            $payment_method2 = $payment_method->setPaymentMethods($response, $branch_id);

            if(!is_null($branch_id)){
                return redirect()->back()->with('success', 'Importación de metodos de pago con exito.');
            }
        }
    }

    //funcion para obtener las unidades de sat
    function getUnidadesSat($branch_id = null){
        $unidad_sat_exist = is_null($branch_id) ? UnidadSat::first():null;
        if(!is_object($unidad_sat_exist)){
            $response = $this->getQuickBase('unidades_sat');
            $unidadSat = new UnidadSat();
            $unidadSat2 = $unidadSat->setUnidades($response);

            if(!is_null($branch_id)){
                return redirect()->back()->with('success', 'Importación de unidades de SAT con exito.');
            }
        }
    }

    //funcion para obtener proveedores
    function getProveedores($branch_id = null){
        $proveedor_exist = is_null($branch_id) ? Proveedor::first():null;
        if(!is_object($proveedor_exist)){
            $response = $this->getQuickBase('proveedores');
            $proveedor = new Proveedor();
            $proveedor2 = $proveedor->setProveedores($response);
        }

        if(!is_null($branch_id)){
            return redirect()->back()->with('success', 'Importación de proveedores con exito.');
        }
    }

    //funcion para saber si existe conexion a internet
    static function hasInternetConnection(): bool
    {
        try {
            $connected = @fsockopen("www.google.com", 80);
            if ($connected) {
                fclose($connected);
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    //funcion para quitar signo de pesos y hacerlo numerico el valor
    function formatNumberr($valor){
        return (float)str_replace(',','', str_replace('$', '', $valor));
    }

    //funcion para db externa
    function db_externa($data, $endpoint){  
        $encoded = base64_encode(json_encode($data));

        $response = Http::withHeaders([
            'Content-Type' => 'text/plain'
        ])->withBody($encoded, 'text/plain')
        ->post("https://tciconsultoria.com/lapequenita/punto_venta_conection_db/{$endpoint}");

        if ($response->successful()) {
            return $response->body();
        } else {
            return response()->json(['error' => 'Error al enviar datos', 'details' => $response->body()], 500);
        }
    }

    //funcion para guardar en DB Externa
    public function saveDb($table, $data){
        $data = [
            'table' => $table,
            'fields' => $data,
        ];

        $db = $this->db_externa($data, 'save_data.php');
        return json_decode($db);
    }

    //funcion para guardar en DB Externa
    public function updateDb($table, $fields, $where){
        $data = [
            'table' => $table,
            'fields' => $fields,
            'where' => $where,
        ];

        $db = $this->db_externa($data, 'update_data.php');
        return json_decode($db);
    }

    //funcion para consultar en DB Externa
    function consultDb($table, $data){
        $data = [
            'table' => $table,
            'where' => $data,
        ];

        $db = $this->db_externa($data, 'get_data.php');
        return json_decode($db);
    }

    //funcion para saber si existen registros en la db
    function existDataDb($table){
        $data = [
            'table' => $table,
        ];

        $db = $this->db_externa($data, 'exist_data.php');
        return json_decode($db);
    }

    //generamos tickets
    public function ticket($id, $auto = false){    
        $empresa = EmpresaDetail::first();
        $logoPath = public_path('img/logo_cliente.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        if(request()->is('ticket-sale/'.$id) || request()->is('ticket-sale/'.$id."/".$auto)){
            $dir = 'tickets_sale';
            $sale = Sale::find($id);
            $lines = count($sale->getDetails ?? []) + 30;
            $pdf = Pdf::loadView('ticket', ['sale' => $sale, 'empresa' => $empresa, 'logoBase64' => $logoBase64]);
        }else if(request()->is('ticket-devolution/'.$id) || request()->is('ticket-devolution/'.$id."/".$auto)){
            $dir = 'tickets_dev';
            $devolucion = Devolucion::find($id);
            $sale = $devolucion->getSale;
            $products = $devolucion->getSale->getDetailsDev;
            $lines = count($products ?? []) + 30;
            $pdf = Pdf::loadView('ticket_devolution', ['devolucion' => $devolucion, 'products' => $products, 'sale' => $sale, 'empresa' => $empresa, 'logoBase64' => $logoBase64]);
        }else{
            $dir = 'tickets_box';
            $user = User::find($id);
            $box = Box::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $number_ventas = Sale::where('user_id', $user->id)->whereBetween('updated_at', [$box->start_date, $box->end_date])->count();
            $pdf = Pdf::loadView('ticket_box', ['user' => $user, 'empresa' => $empresa, 'box' => $box, 'number_ventas' => $number_ventas, 'logoBase64' => $logoBase64]);
        }

        

        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        $alto = isset($lines) ? (($lines * 15)+50):500;
        $pdf->setPaper([0, 0, 226.77, $alto], 'portrait'); // 80mm de ancho (~226.77pt)
        $pdf->setOption('isRemoteEnabled', true);
        
        if($auto){
            $path = $this->imprPdf($pdf, $dir);
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="ticket.pdf"',
            ]);
        }

        return $pdf->stream($dir,'/ticket.pdf');
    }

    //fucnion para imprimir automatico
    private function imprPdf($pdf, $dir){
        try {
            $pdfPath = tempnam(sys_get_temp_dir(), 'ticket_').'.pdf';
            $pdf->save($pdfPath);
            $sumatraPath = base_path('SumatraPDF.exe');
            exec("\"$sumatraPath\" -print-to-default \"$pdfPath\"");
            return $pdfPath;
        } catch (\Throwable $th) {
            dd('Ocurrio un error inesperado.');
        }
    }

    //funcion para guardar venta en db externa
    public function saveSaleDBExt($sale){
        $data_db[0]['sale_id'] = $sale->id;
        $data_db[0]['date'] = $sale->date;
        $data_db[0]['folio'] = $sale->folio; //cambia a no unico
        $data_db[0]['user'] = $sale->getUser->name; //cambia a string
        $data_db[0]['branch_id'] = $sale->branch_id;
        $data_db[0]['uuid'] = $sale->uuid;
        $data_db[0]['payment_method_id'] = $sale->payment_method_id;
        $data_db[0]['type_payment'] = $sale->type_payment;
        $data_db[0]['amount_received'] = $sale->amount_received;
        $data_db[0]['change_'] = $sale->change;
        $data_db[0]['sat_document_type'] = $sale->sat_document_type;
        $data_db[0]['total_sale'] = $sale->total_sale;
        $data_db[0]['coin'] = $sale->coin;
        $data_db[0]['status'] = $sale->status;
        $data_db[0]['customer'] = $sale->getClient->name; //cambia a string
        $data_db[0]['created_at'] = $sale->created_at->format('d-m-Y H:i:s');
        $data_db[0]['updated_at'] = $sale->updated_at->format('d-m-Y H:i:s');
        $data_db[0]['details_json'] = json_encode($sale->getDetails->toArray());
        $data_db[0]['detail_cant_json'] = json_encode($sale->getDetailsCant->toArray());

        try {
            $this->saveDb('sales', $data_db);
            Log::info('Venta guardada');
        } catch (\Throwable $th) {
            Log::error('Error al guardar la venta', ['error' => $th->getMessage()]);
        }
    }

    //funcion para guardar devolucion en db externa
    public function saveDevolutionDBExt($devolution, $update){
        $data_db[0]['devolucion_id'] = $devolution->id;
        $data_db[0]['sale_id'] = $devolution->sale_id;
        $data_db[0]['branch_id'] = $devolution->branch_id;
        $data_db[0]['user'] = $devolution->getUser->name;
        $data_db[0]['cantidad'] = $devolution->cantidad;
        $data_db[0]['description'] = $devolution->description;
        $data_db[0]['fecha_devolucion'] = $devolution->fecha_devolucion;
        $data_db[0]['total_descuentos'] = $devolution->total_descuentos;
        $data_db[0]['total_devolucion'] = $devolution->total_devolucion;
        $data_db[0]['status'] = 1;
        $data_db[0]['created_at'] = $devolution->created_at;
        $data_db[0]['updated_at'] = $devolution->updated_at;
        
        $data_db[0]['details_json'] = json_encode($devolution->getSale->getDetailsDev);
        $data_db[0]['details_cant_json'] = json_encode($devolution->getSale->getDetailsCantDev);

        try {
            if($update){
                $data_db = $data_db[0];
                $where['devolucion_id'] = $devolution->id;
                $this->updateDb('devoluciones', $data_db, $where);
                Log::info('Devolución actualizada');
            }else{
                $this->saveDb('devoluciones', $data_db);
                Log::info('Devolución guardada');
            }
        } catch (\Throwable $th) {
            Log::error('Error al guardar la devolución', ['error' => $th->getMessage()]);
        }
    }

    //funcion para guardar venta en db externa
    public function saveCompraDBExt($compra, $update){
        $data_db[0]['compra_id'] = $compra->id;
        $data_db[0]['folio'] = $compra->folio;
        $data_db[0]['branch_id'] = $compra->branch_id;
        $data_db[0]['proveedor_id'] = $compra->proveedor_id;
        $data_db[0]['user'] = $compra->getUser->name;
        $data_db[0]['programacion_entrega'] = $compra->programacion_entrega;
        $data_db[0]['fecha_recibido'] = $compra->fecha_recibido;
        $data_db[0]['plazo'] = $compra->plazo;
        $data_db[0]['fecha_vencimiento'] = $compra->fecha_vencimiento;
        $data_db[0]['moneda'] = $compra->moneda;
        $data_db[0]['tipo'] = $compra->tipo;
        $data_db[0]['importe'] = $compra->importe ?? 0;
        $data_db[0]['impuesto_productos'] = $compra->impuesto_productos ?? 0;
        $data_db[0]['descuentos'] = $compra->descuentos ?? 0;
        $data_db[0]['subtotal'] = $compra->subtotal ?? 0;
        $data_db[0]['total'] = $compra->total ?? 0;
        $data_db[0]['observaciones'] = $compra->observaciones;
        $data_db[0]['status'] = $compra->status ?? 1;
        $data_db[0]['created_at'] = $compra->created_at;
        $data_db[0]['updated_at'] = $compra->updated_at;

        $data_db[0]['details_json'] = json_encode($compra->getDetalles->toArray());
        $data_db[0]['details_cant_json'] = json_encode($compra->getDetallesEntra->toArray());

        try {
            if($update){
                $data_db = $data_db[0];
                $where['compra_id'] = $compra->id;
                $this->updateDb('compras', $data_db, $where);
                Log::info('Compra actualizada');
            }else{
                $this->saveDb('compras', $data_db);
                Log::info('Compra guardada');
            }
        } catch (\Throwable $th) {
            Log::error('Error al guardar la compra', ['error' => $th->getMessage()]);
        }
    }

    //funcion para consultar ultima venta y almacenar ventas pendientes
    public function getSales($sales){
        foreach($sales ?? [] as $item){
            $data['sale_id'] = $item->id;
            $response = $this->consultDb('sales', $data);
           
            if($response->status != 'success'){
                $ctrl = new \App\Http\Controllers\Controller();
                $ctrl->saveSaleDBExt($item);
            }
        }
    }

    //funcion para consultar las devoluciones de 7 dias atras y almacenar ventas pendientes
    public function getDevoluciones($devoluciones){
        foreach($devoluciones ?? [] as $item){
            $data['devolucion_id'] = $item->id;
            $response = $this->consultDb('devoluciones', $data);
           
            if($response->status != 'success'){
                $ctrl = new \App\Http\Controllers\Controller();
                $ctrl->saveDevolutionDBExt($item, false);
            }
        }
    }

    //funcion para consultar las compras de 7 dias atras y almacenar compras pendientes
    public function getCompras($compras){
        foreach($compras ?? [] as $item){
            $data['compra_id'] = $item->id;
            $response = $this->consultDb('compras', $data);
           
            if($response->status != 'success'){
                $ctrl = new \App\Http\Controllers\Controller();
                $ctrl->saveCompraDBExt($item, false);
            }
        }
    }

    //funcion para regresar a vista anterior
    public function makeMigration(){
        if($this->hasInternetConnection()){
            Artisan::call('migrate', [
            '--force' => true // Necesario para ejecución sin confirmación
            ]);

            return redirect()->back()->with('success', 'Completado');
        }
    }
}
 