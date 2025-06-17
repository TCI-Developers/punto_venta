<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\{DB,Auth, Http};
use App\Models\{Product, Brand, Sale, PaymentMethod, UnidadSat, Driver, Proveedor, EmpresaDetail, User, Box};
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
            $data['db'] = 'bqbrd7fy7';
            $data['query'] = '';
            $data['clist'] = [15,17,16,13,6];
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
    function hasInternetConnection(): bool
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

    //funcion para consultar en DB Externa
    function saveDb($table, $data){
        $data = [
            'table' => $table,
            'fields' => $data,
        ];

        $db = $this->db_externa($data, 'save_data.php');
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
    public function ticket($id){    
        $alto = 500;  
        $empresa = EmpresaDetail::first();
        if(request()->is('ticket-sale/'.$id)){
            $sale = Sale::find($id);
            $lines = count($sale->getDetails ?? []) + 30;
            $alto = ($lines * 15) + 50;
            $pdf = Pdf::loadView('ticket', ['sale' => $sale, 'empresa' => $empresa]);
        }else{
            $user = User::find($id);
            $box = Box::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $number_ventas = Sale::where('user_id', $user->id)->whereBetween('updated_at', [$box->start_date, $box->end_date])->count();
            $pdf = Pdf::loadView('ticket_box', ['user' => $user, 'empresa' => $empresa, 'box' => $box, 'number_ventas' => $number_ventas]);
        }

        $pdf->setPaper([0, 0, 226.77, $alto], 'portrait'); // 80mm de ancho (~226.77pt)
        $pdf->setOption('isRemoteEnabled', true);
        return $pdf->stream("ticket.pdf");
    }

    // public function ticket2(){   
    //     $empresa = EmpresaDetail::first();
    //     $pdf = Pdf::loadView('ticket2', ['empresa' => $empresa]);
    //     $pdf->setPaper([0, 0, 226.77, 500], 'portrait'); // 80mm de ancho (~226.77pt)
    //     $pdf->setOption('isRemoteEnabled', true);
    //     // $pdf->save(storage_path('ticket.pdf'));
    //     exec('start /min "" "ticket.pdf" /p /h');
    //     return $pdf->stream("ticket.pdf");
    //     // return redirect()->back();
    // }
}
 