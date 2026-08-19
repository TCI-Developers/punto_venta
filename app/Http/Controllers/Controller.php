<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\{DB,Auth, Http, Storage, Log, Artisan, Crypt};
use App\Models\{Product, Brand, Sale, PaymentMethod, UnidadSat, Driver, Proveedor, EmpresaDetail, User, Box, Devolucion, DevolucionMatriz, BranchUser, Gasto, Factura};
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

        $userToken = config('services.quickbase.user_token');
        $sortOrder = [["fieldId" => 3,"order" => "ASC"],];

        $url = "https://api.quickbase.com/v1/records/query";

        $headers = [
            "QB-Realm-Hostname: ".config('services.quickbase.dominio').".quickbase.com",
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
            $connected = @fsockopen("www.google.com", 80, $errno, $errstr, 2);
            if ($connected) {
                fclose($connected);
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    //actualiza el precio de las presentaciones (parts_to_product) de los productos en $codes,
    //para que queden igual que el producto base recien actualizado. Formula compartida con
    //ProductsImport (carga de Excel) y usada por los 2 caminos que actualizan precios de
    //productos (syncProductPrices en el login, runCatalogSync en el sync/banner de catalogo):
    //si la presentacion tiene despiece, precio_despiece/cantidad_despiezado; si no, el precio
    //normal. Tambien cascadea price_mayoreo y cantidad_mayoreo (minimo de piezas para que
    //aplique mayoreo, ahora gestionado desde la Matriz igual que el resto de los precios).
    //No toca cantidad_despiezado (estructura de despiece) ni code_bar de la presentacion.
    protected function cascadePresentationPrices(array $codes): void
    {
        $codes = array_values(array_filter($codes, fn($c) => trim((string)$c) !== ''));
        if (!count($codes)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $sql = "UPDATE parts_to_product SET
            price = CASE
                WHEN cantidad_despiezado > 0 THEN (SELECT precio_despiece FROM products WHERE products.id = parts_to_product.product_id) / cantidad_despiezado
                ELSE (SELECT precio FROM products WHERE products.id = parts_to_product.product_id)
            END,
            price_mayoreo = (SELECT precio_mayoreo FROM products WHERE products.id = parts_to_product.product_id),
            cantidad_mayoreo = (SELECT cantidad_mayoreo FROM products WHERE products.id = parts_to_product.product_id)
            WHERE product_id IN (SELECT id FROM products WHERE code_product IN ({$placeholders}))";

        DB::update($sql, $codes);
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

    //funcion para consumir la API de Matriz (reemplaza al bridge db_externa para sync de
    //ventas/devoluciones/compras/cuentas por pagar/catalogo). El bridge db_externa() se deja
    //intacto como fallback hasta validar que todo funcione con Matriz.
    protected function matrizApi(string $method, string $endpoint, array $params = []): \Illuminate\Http\Client\Response
    {
        return Http::withToken($this->getMatrizToken())
            ->acceptJson()
            ->timeout(15)
            ->{$method}(config('services.matriz.url') . '/api/pos/' . $endpoint, $params);
    }

    //el token se puede guardar cifrado en empresa_details (editable desde la pantalla de
    //Empresa, sin tocar archivos) para poder rotarlo si se invalida sin necesitar acceso al
    //.env de esa PC. Si no hay uno guardado ahi, cae al de config/.env como antes.
    protected function getMatrizToken(): ?string
    {
        $empresa = EmpresaDetail::first();

        if ($empresa && !empty($empresa->matriz_token)) {
            try {
                return Crypt::decrypt($empresa->matriz_token);
            } catch (\Throwable $th) {
                Log::error('No se pudo desencriptar el token de Matriz guardado en empresa_details: '.$th->getMessage());
            }
        }

        return config('services.matriz.token');
    }

    //busca a UN usuario especifico en el catalogo de Matriz y actualiza solo su branch_user
    //local. Se usa cuando localmente no tiene acceso a la sucursal, para revisar en el momento
    //si Matriz ya le dio acceso, sin esperar a que alguien corra la sincronizacion completa del
    //catalogo. Devuelve true si encontro al usuario en Matriz (independiente de que sucursales
    //le hayan asignado), false si no se pudo consultar o no existe alla.
    public function syncUserBranchFromMatriz($user){
        try {
            if(!$this->hasInternetConnection()){
                return false;
            }

            $response = $this->matrizApi('get', 'catalogo');
            if(!$response->successful()){
                return false;
            }

            $usuarios = $response->json('usuarios') ?? [];
            $match = collect($usuarios)->firstWhere('email', $user->email);

            // se borra el acceso via 'matriz' de este usuario en ambos casos: si ya no aparece
            // en el catalogo (se lo quitaron alla) se revoca; si aparece, se reemplaza por lo
            // vigente. Nunca toca filas con source distinto (asignadas a mano en POSTCI).
            BranchUser::where('user_id', $user->id)->where('source', 'matriz')->delete();

            if(!$match){
                return false;
            }

            foreach($match['sucursal_ids'] ?? [] as $branchId){
                $branch_user = new BranchUser();
                $branch_user->user_id = $user->id;
                $branch_user->branch_id = $branchId;
                $branch_user->source = 'matriz';
                $branch_user->save();
            }

            return true;
        } catch (\Throwable $th) {
            Log::warning('No se pudo sincronizar el usuario contra Matriz: '.$th->getMessage());
            return false;
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

    //funcion para consultar en DB Externa
    function consultDbPaginate($table, $page, $per_page){
        $data = [
            'table' => $table,
            'page' => $page,
            'per_page' => $per_page,
        ];

        $db = $this->db_externa($data, 'get_data_paginate.php');
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

    //arma un data URI con el mime real del archivo (no asumido por la extension) -- un logo
    //guardado como .png que en realidad es un jpg (u otro formato) rompe el render en PDF si
    //se le pone un mime incorrecto.
    protected function logoDataUri(string $logoPath): ?string
    {
        if (!file_exists($logoPath)) {
            return null;
        }
        $mime = mime_content_type($logoPath) ?: 'image/png';
        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($logoPath));
    }

    //generamos tickets
    public function ticket($id, $auto = false){
        $empresa = EmpresaDetail::first();
        $logoPath = public_path('img/logo_cliente.png');
        $logoBase64 = $this->logoDataUri($logoPath);

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
        }else if(request()->is('ticket-devolution-matriz/'.$id) || request()->is('ticket-devolution-matriz/'.$id."/".$auto)){
            $dir = 'tickets_dev_matriz';
            $devolucion = DevolucionMatriz::find($id);
            $compra = $devolucion->getCompra;
            $lines = 31;
            $pdf = Pdf::loadView('ticket_devolution_matriz', ['devolucion' => $devolucion, 'compra' => $compra, 'empresa' => $empresa, 'logoBase64' => $logoBase64]);
        }else if(request()->is('ticket-gasto/'.$id) || request()->is('ticket-gasto/'.$id."/".$auto)){
            $dir = 'tickets_gasto';
            $gasto = Gasto::find($id);
            $lines = 20;
            $pdf = Pdf::loadView('ticket_gasto', ['gasto' => $gasto, 'empresa' => $empresa, 'logoBase64' => $logoBase64]);
        }else{
            $dir = 'tickets_box';
            $user = User::find($id);
            $box = Box::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $number_ventas = Sale::where('user_id', $user->id)->whereBetween('updated_at', [$box->start_date, $box->end_date])->count();

            // rango de folios internos (id con prefijo) del turno -- no es un folio fiscal
            // secuencial real, solo referencia de que rango de ventas/facturas cayo en el turno.
            $folio_venta_inicial = Sale::where('user_id', $user->id)->whereBetween('updated_at', [$box->start_date, $box->end_date])->min('id');
            $folio_venta_final = Sale::where('user_id', $user->id)->whereBetween('updated_at', [$box->start_date, $box->end_date])->max('id');
            $folio_factura_inicial = Factura::where('user_id', $user->id)->where('status', 1)->whereBetween('created_at', [$box->start_date, $box->end_date])->min('id');
            $folio_factura_final = Factura::where('user_id', $user->id)->where('status', 1)->whereBetween('created_at', [$box->start_date, $box->end_date])->max('id');

            $lines = 55; // el ticket ahora incluye ingresos/egresos/folios/arqueo, ya no cabe en el alto fijo original

            $pdf = Pdf::loadView('ticket_box', [
                'user' => $user, 'empresa' => $empresa, 'box' => $box, 'number_ventas' => $number_ventas, 'logoBase64' => $logoBase64,
                'folio_venta_inicial' => $folio_venta_inicial, 'folio_venta_final' => $folio_venta_final,
                'folio_factura_inicial' => $folio_factura_inicial, 'folio_factura_final' => $folio_factura_final,
            ]);
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

    //arma el payload de una venta para Matriz (reusado por saveSaleDBExt y el lote de getSales)
    private function buildSalePayload($sale){
        return [
            'sale_id' => $sale->id,
            'date' => $sale->created_at->toDateTimeString(), // fecha+hora real, $sale->date solo trae la fecha
            'folio' => $sale->folio, //cambia a no unico
            'user' => $sale->getUser->name, //cambia a string
            'branch_id' => $sale->branch_id,
            'uuid' => $sale->uuid,
            'payment_method_id' => $sale->payment_method_id,
            'type_payment' => $sale->type_payment,
            'monto_efectivo' => $sale->monto_efectivo, // solo llenos cuando type_payment = 'mixto'
            'monto_tarjeta' => $sale->monto_tarjeta,
            'amount_received' => $sale->amount_received,
            'change_' => $sale->change,
            'sat_document_type' => $sale->sat_document_type,
            'total_sale' => $sale->total_sale,
            'coin' => $sale->coin,
            'status' => $sale->status,
            'customer' => $sale->getClient->name, //cambia a string
            'created_at' => $sale->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $sale->updated_at->format('d-m-Y H:i:s'),
            'details_json' => json_encode($sale->getDetails->toArray()),
            'detail_cant_json' => json_encode($sale->getDetailsCant->toArray()),
        ];
    }

    //funcion para guardar venta en db externa
    public function saveSaleDBExt($sale){
        try {
            $this->matrizApi('post', 'sales', ['sales' => [$this->buildSalePayload($sale)]]);
            Log::info('Venta guardada');
        } catch (\Throwable $th) {
            Log::error('Error al guardar la venta', ['error' => $th->getMessage()]);
        }
    }

    //arma el payload de una devolucion para Matriz
    private function buildDevolutionPayload($devolution){
        return [
            'devolucion_id' => $devolution->id,
            'sale_id' => $devolution->sale_id,
            'branch_id' => $devolution->branch_id,
            'user' => $devolution->getUser->name,
            'cantidad' => $devolution->cantidad,
            'description' => $devolution->description,
            'fecha_devolucion' => $devolution->fecha_devolucion,
            'total_descuentos' => $devolution->total_descuentos,
            'total_devolucion' => $devolution->total_devolucion,
            'monto_efectivo' => $devolution->monto_efectivo, // solo llenos cuando la venta original fue 'mixto'
            'monto_tarjeta' => $devolution->monto_tarjeta,
            'status' => 1,
            'created_at' => $devolution->created_at,
            'updated_at' => $devolution->updated_at,
            'details_json' => json_encode($devolution->getSale->getDetailsDev),
            'details_cant_json' => json_encode($devolution->getSale->getDetailsCantDev),
        ];
    }

    //funcion para guardar devolucion en db externa
    public function saveDevolutionDBExt($devolution, $update){
        try {
            $this->matrizApi('post', 'devoluciones', ['devoluciones' => [$this->buildDevolutionPayload($devolution)]]);
            Log::info($update ? 'Devolución actualizada' : 'Devolución guardada');
        } catch (\Throwable $th) {
            Log::error('Error al guardar la devolución', ['error' => $th->getMessage()]);
        }
    }

    //arma el payload de una compra para Matriz
    private function buildCompraPayload($compra){
        return [
            'compra_id' => $compra->id,
            'folio' => $compra->folio,
            'branch_id' => $compra->branch_id,
            'proveedor_id' => $compra->proveedor_id,
            'user' => $compra->getUser->name ?? $compra->user,
            'programacion_entrega' => $compra->programacion_entrega,
            'fecha_recibido' => $compra->fecha_recibido,
            'plazo' => $compra->plazo,
            'fecha_vencimiento' => $compra->fecha_vencimiento,
            'moneda' => $compra->moneda,
            'tipo' => $compra->tipo,
            'importe' => $compra->importe ?? 0,
            'impuesto_productos' => $compra->impuesto_productos ?? 0,
            'descuentos' => $compra->descuentos ?? 0,
            'subtotal' => $compra->subtotal ?? 0,
            'total' => $compra->total ?? 0,
            'observaciones' => $compra->observaciones,
            'status' => $compra->status ?? 1,
            'created_at' => $compra->created_at,
            'updated_at' => $compra->updated_at,
            'details_json' => json_encode($compra->getDetalles->toArray()),
            'details_cant_json' => json_encode($compra->getDetallesEntra->toArray()),
        ];
    }

    //funcion para guardar venta en db externa
    public function saveCompraDBExt($compra, $update){
        try {
            $this->matrizApi('post', 'compras', ['compras' => [$this->buildCompraPayload($compra)]]);
            Log::info($update ? 'Compra actualizada' : 'Compra guardada');
        } catch (\Throwable $th) {
            Log::error('Error al guardar la compra', ['error' => $th->getMessage()]);
        }
    }

    //arma el payload de una cuenta por pagar para Matriz. $compra/$detalle_compra son opcionales
    //-- si no se pasan (ej. al re-sincronizar despues de registrar un pago, cuando solo se tiene
    //el $cxp a la mano), se resuelven por relacion.
    private function buildCXPPayload($cxp, $compra = null, $detalle_compra = null){
        $compra = $compra ?? $cxp->getCompra;
        $detalle_compra = $detalle_compra ?? ($compra ? $compra->getDetalles : collect());

        return [
            'compra_id' => $cxp->id,
            'branch_id' => $cxp->branch_id,
            'date' => date('Y-m-d', strtotime($cxp->created_at)),
            'fecha_vencimiento' => $cxp->fecha_vencimiento,
            'subtotal' => $cxp->subtotal,
            'impuestos' => $cxp->impuestos,
            'total' => $cxp->total,
            'status' => $cxp->status == 2 ? 0 : 1, // POSTCI: 1=activa,2=pagada -> Matriz: 1=pendiente,0=pagada
            'compra_json' => json_encode($compra ? $compra->toArray() : []),
            'compra_details_json' => json_encode($detalle_compra->toArray()),
        ];
    }

    //funcion para guardar cuenta por pagar a db externa
    public function saveCXPDBExt($cxp, $compra = null, $detalle_compra = null){
        if((int)$cxp->status === 0){
            return; // eliminado logico local, no se sincroniza con Matriz
        }

        try {
            $this->matrizApi('post', 'cuentas-pagar', ['cuentas' => [$this->buildCXPPayload($cxp, $compra, $detalle_compra)]]);
            Log::info('Cuanta por pagar guardada');
        } catch (\Throwable $th) {
            Log::error('Error al guardar la cuenta por pagar', ['error' => $th->getMessage()]);
        }
    }

    //funcion para sincronizar en un solo lote las ventas de los ultimos dias. Antes hacia un
    //GET /check + POST individual por cada una -- con un backlog grande (ej. 22 compras) esto
    //podia tardar minutos. El endpoint ya es idempotente e informa cuantas inserto/salto
    //({"inserted":X,"skipped":Y}), asi que se manda todo en una sola llamada.
    public function getSales($sales){
        if(!count($sales ?? [])){
            return;
        }

        $batch = array_map(fn($item) => $this->buildSalePayload($item), $sales instanceof \Illuminate\Support\Collection ? $sales->all() : $sales);

        try {
            $response = $this->matrizApi('post', 'sales', ['sales' => $batch]);
            Log::info(count($batch).' ventas sincronizadas en lote', ['response' => $response->json()]);
        } catch (\Throwable $th) {
            Log::error('Error al sincronizar ventas en lote', ['error' => $th->getMessage()]);
        }
    }

    //funcion para sincronizar en un solo lote las devoluciones de los ultimos dias
    public function getDevoluciones($devoluciones){
        if(!count($devoluciones ?? [])){
            return;
        }

        $batch = array_map(fn($item) => $this->buildDevolutionPayload($item), $devoluciones instanceof \Illuminate\Support\Collection ? $devoluciones->all() : $devoluciones);

        try {
            $response = $this->matrizApi('post', 'devoluciones', ['devoluciones' => $batch]);
            Log::info(count($batch).' devoluciones sincronizadas en lote', ['response' => $response->json()]);
        } catch (\Throwable $th) {
            Log::error('Error al sincronizar devoluciones en lote', ['error' => $th->getMessage()]);
        }
    }

    //funcion para sincronizar en un solo lote las compras de los ultimos dias
    public function getCompras($compras){
        if(!count($compras ?? [])){
            return;
        }

        $batch = array_map(fn($item) => $this->buildCompraPayload($item), $compras instanceof \Illuminate\Support\Collection ? $compras->all() : $compras);

        try {
            $response = $this->matrizApi('post', 'compras', ['compras' => $batch]);
            Log::info(count($batch).' compras sincronizadas en lote', ['response' => $response->json()]);
        } catch (\Throwable $th) {
            Log::error('Error al sincronizar compras en lote', ['error' => $th->getMessage()]);
        }
    }

    //arma el payload de un gasto de caja para Matriz
    private function buildGastoPayload($gasto){
        return [
            'gasto_id' => $gasto->id,
            'box_id' => $gasto->box_id,
            'branch_id' => $gasto->branch_id,
            'user' => $gasto->getUser->name ?? null,
            'concepto' => $gasto->concepto,
            'monto' => $gasto->monto,
            'description' => $gasto->description,
            'status' => $gasto->status,
            'created_at' => $gasto->created_at,
            'updated_at' => $gasto->updated_at,
        ];
    }

    //funcion para guardar gasto en db externa
    public function saveGastoDBExt($gasto){
        try {
            $this->matrizApi('post', 'gastos', ['gastos' => [$this->buildGastoPayload($gasto)]]);
            Log::info('Gasto guardado');
        } catch (\Throwable $th) {
            Log::error('Error al guardar el gasto', ['error' => $th->getMessage()]);
        }
    }

    //funcion para sincronizar en un solo lote los gastos modificados recientemente. Igual que
    //cuentas-pagar, no hay endpoint /check en Matriz, pero el POST es idempotente por gasto_id.
    public function getGastos($gastos){
        if(!count($gastos ?? [])){
            return;
        }

        $batch = array_map(fn($item) => $this->buildGastoPayload($item), $gastos instanceof \Illuminate\Support\Collection ? $gastos->all() : $gastos);

        try {
            $response = $this->matrizApi('post', 'gastos', ['gastos' => $batch]);
            Log::info(count($batch).' gastos sincronizados en lote', ['response' => $response->json()]);
        } catch (\Throwable $th) {
            Log::error('Error al sincronizar gastos en lote', ['error' => $th->getMessage()]);
        }
    }

    //funcion para sincronizar en un solo lote las cuentas por pagar modificadas recientemente.
    //A diferencia de sales/devoluciones/compras, cuentas-pagar no tiene endpoint /check en
    //Matriz -- pero el POST ya es idempotente por compra_id, asi que reenviar no duplica nada.
    public function getCuentasPagar($cuentas){
        $batch = [];
        foreach($cuentas ?? [] as $item){
            if((int)$item->status === 0){
                continue; // eliminado logico local, no se sincroniza
            }
            $batch[] = $this->buildCXPPayload($item);
        }

        if(!count($batch)){
            return;
        }

        try {
            $response = $this->matrizApi('post', 'cuentas-pagar', ['cuentas' => $batch]);
            Log::info(count($batch).' cuentas por pagar sincronizadas en lote', ['response' => $response->json()]);
        } catch (\Throwable $th) {
            Log::error('Error al sincronizar cuentas por pagar en lote', ['error' => $th->getMessage()]);
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
 