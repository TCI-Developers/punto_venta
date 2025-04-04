<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\{User, Product, Brand, Customer, PaymentMethod, UnidadSat, Role, Turno, UserRole, Box, Driver, Proveedor};

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct(){

        $this->middleware(function ($request, $next) {
            $controller = class_basename($request->route()->getController());
            $controllers_excluidos = ['AdminController','BranchController', 'UserController', 'RoleController', 'Controller'];
            $ban = 0;
            foreach($controllers_excluidos as $item){
                if($item == $controller){
                    $ban = 1;
                    break;
                }
            }

            if(!$ban && $this->sucursalUser() === false){
                return redirect()->route('branchs.index')->with('error', 'Selecciona una sucursal para poder acceder al sistema.');
            }
            
            return $next($request);
        });
    }

    //funcion Curl QuickBase
    function getQuickBase($table_name_db, $data = null){
        $db = $this->validacionTabla($table_name_db, $data)['db'];
        $query = $this->validacionTabla($table_name_db, $data)['query'];
        $clist = $this->validacionTabla($table_name_db, $data)['clist'];

        $url = "https://aortizdemontellanoarevalo.quickbase.com/db/".$db; //url a donde se consulta
        $userToken = "b8degy_fwjc_0_djjg8pab6ss873bfjuhnjb6vdbut";

        $bodyQuery = "<qdbapi> 
            <usertoken>".$userToken."</usertoken>
            <query>".$query."</query>
            <clist>".$clist."</clist>
        </qdbapi>"; //consulta para obtener los productos

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
        curl_setopt($ch, CURLOPT_POSTFIELDS,$bodyQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml',
            'Content-Length:',
            'QUICKBASE-ACTION: API_DoQuery'
        ));
        
        $response = curl_exec($ch);
        curl_close ($ch);
        $record = simplexml_load_string($response);
        $values = array();
        foreach($record->record as $value){
            $values[] = $value;
        }
        return  $values;
    }

    //funcion para obtener los parametros que ocupara la consulta de quickbase
    function validacionTabla($table_name_db, $data = null){
        if($table_name_db == 'sucursales'){
            $data['db'] = 'bqbrd7fy7';
            $data['query'] = '';
            $data['clist'] = '15.17.16.13.6';
        }else if($table_name_db == 'usuarios'){
            $data['db'] = 'brnx9pgfy';
            $data['query'] = "{30.EX.'".$data['tel']."'} AND {8.EX.'".$data['pass']."'}";
            $data['clist'] = '28.6.30.8';
        }else if($table_name_db == 'brands'){
            $data['db'] = 'brer52xt3';
            $data['query'] = '';
            $data['clist'] = '3.6.7';
        }else if($table_name_db == 'payment_methods'){
            $data['db'] = 'bqgubmjca';
            $data['query'] = '';
            $data['clist'] = '3.6.7';
        }else if($table_name_db == 'unidades_sat'){
            $data['db'] = 'bqgt9zstu';
            $data['query'] = '';
            $data['clist'] = '3.6.7.8';
        }else if($table_name_db == 'productos'){
            $data['db'] = 'bqa4qy4jd';
            $data['query'] = '{86.EX.0}AND{82.EX.0}';
            $data['clist'] = '3.13.29.154.43.92.86.49.155.64.65.66.67.44.79.60';
        }else if($table_name_db == 'drivers'){
            $data['db'] = 'bqa4qy3yt';
            $data['query'] = '{53.EX.0}AND{127.EX.8}';
            $data['clist'] = '3.10';
        }else if($table_name_db == 'proveedores'){
            $data['db'] = 'bqa4qy387';
            $data['query'] = '';
            $data['clist'] = '3.17.6.8.16.19.18.28.29.20.30';
        }
       
        return $data;
    }

    //funcion para saber si el usuario tiene sucursal asignada para entrar a mas modulos
    public function sucursalUser(){
            $user = Auth::user();
            return $user && $user->branch_id ? true : false;
    }

    //funcion para obtener las marcas (linea productos)
    function getDrivers($branch_id = null){
        $driver_exist = is_null($branch_id) ? Driver::first():null;
        if(!is_object($driver_exist)){
            $response = $this->getQuickBase('drivers');
            $driver = new Driver();
            $driver2 = $driver->setDrivers($response);

            if(!is_null($branch_id)){
                return redirect()->back()->with('success', 'Importación de choferes con exito.');
            }
        }
    }

    //funcion para obtener las marcas (linea productos)
    function getBrands($branch_id = null){
        $brand_exist = is_null($branch_id) ? Brand::first():null;
        if(!is_object($brand_exist)){
            // $db = 'brer52xt3';
            // $query = '';
            // $clist = '3.6.7';
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
            // $db = 'bqa4qy4jd';
            // $query = '{86.EX.0}AND{82.EX.0}';
            // $clist = '3.13.29.154.43.92.86.49.155.64.65.66.67.44.79.60';
            $response = $this->getQuickBase('productos');
            $product = new Product();
            $product2 = $product->setProducs($response, $branch_id);
        }
    }

    //funcion para obtener las metodos de pago
    function getPaymentMethods($branch_id = null){
        $payment_method_exist = is_null($branch_id) ? PaymentMethod::first():null;
        if(!is_object($payment_method_exist)){
            // $db = 'bqgubmjca';
            // $query = '';
            // $clist = '3.6.7';
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
            // $db = 'bqgt9zstu';
            // $query = '';
            // $clist = '3.6.7.8';
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
            // Intentar conectarse a Google
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
}
