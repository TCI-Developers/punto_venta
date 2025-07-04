<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Role, UserRole, Customer};
use Illuminate\Support\Facades\{Auth, Hash, Artisan, File, Http};
use Database\Seeders\DatabaseSeeder;

class RootController extends Controller
{
    //vista principal
    public function index()
    {
        return view('Admin.root.index');
    }

    //funcion para obtener de quick y cargar data a db externa
    public function setDataDB($table){
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');

        try {        
            $data_exist = $this->existDataDb($table);
            $data = $this->getQuickBase($table);

            if(isset($data_exist->status) && $data_exist->status){
                $data = $this->addNewDataDB($table, $data);
                if(!count($data)){
                    return redirect()->back()->with('info', 'No existe información para importar.');
                }
            }

            $data_db = $this->inputsDb($table, $data);
            $this->saveDb($table, $data_db);

            return redirect()->back()->with('success', 'Importación con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La importación no se pudo completar.');
        }
    }

    //funcion para importar los registros de la db externa a la db local
    public function setDataDBLocal($modelName, $table){
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');

        try {
            $model = app("App\Models\\{$modelName}");
            $data_exist = $model::first();

            $data = $this->consultDb($table, '');
            if(is_object($data_exist)){
                $data = $this->addNewDataDBLocal($table, $model, $data);
                if(!count($data)){
                    return redirect()->back()->with('info', 'No existe información para importar.');
                }
            }

            $data = isset($data->status) ? $data->data:$data;
            foreach($data as $item){
                $model::create((array)$item);
            }

            return redirect()->back()->with('success', 'Importación con exito.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'La importación no se pudo completar.');
        }
    }

     //funcion para importar las configuracion incial
    public function setConfDBLocal(Request $request){
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');

        try {
            $user = Auth::User();
            if($user->name == 'TCI_DEV' && Hash::check($request->password, $user->password) && $this->hasInternetConnection()){
                $this->setDataDBLocal('Role', 'roles');
                
                $cliente = new Customer();
                $cliente->name = 'Publico General';
                $cliente->save();

                $rol = Role::where('name', 'root')->first();
                $role_user = UserRole::where('user_id', $user->id)->where('role_id', $rol->id)->first();

                if(!is_object($role_user)){
                    $user_role = new UserRole();
                    $user_role->user_id = $user->id;
                    $user_role->role_id = $rol->id;
                    $user_role->save();
                }

                $seeder = new DatabaseSeeder();
                $seeder->run();

                //descargar logos e icono
                $path_logo = public_path('img/logo_cliente.png');
                $path_pdf = base_path('SumatraPDF.exe');

                $response = Http::get(env('URL_LOGO'));
                if ($response->successful()) {
                    File::put($path_logo, $response->body());
                }

                $response = Http::get(env('URL_PDF'));
                if ($response->successful()) {
                    File::put($path_pdf, $response->body());
                }

                return redirect()->back()->with('success', 'Configuración importada con exito.');
            }
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Algo salio mal.');
        }
    }

    function addNewDataDB($table, $data){
        try {
            $keys = $this->keysTable($table);
            $dataExterna = $this->consultDb($table, '');
            $dataExterna = array_column($dataExterna->data, $keys['dbExt']);
            $newData = [];
            foreach($data ?? [] as $item){
                $ban = 0;
                foreach($dataExterna as $val){
                    if((int)$item->{$keys['qb']} === (int)$val){
                        $ban = 1;
                        break;
                    }

                }
                if($ban == 0){
                    $newData[] = $item;
                }
            }

            return $newData ?? [];

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function addNewDataDBLocal($table, $model, $data){
        try {
            $keys = $this->keysTable($table);
            $dbLocal = $model::get()->toArray();
            $dbLocal = array_column($dbLocal, $keys['dbExt']);
            $newData = [];
            foreach($data->data ?? [] as $item){
                $ban = 0;
                foreach($dbLocal as $val){
                    if($item->{$keys['dbExt']} === $val){
                        $ban = 1;
                        break;
                    }
                }
                if($ban == 0){
                    $newData[] = $item;
                }
            }

            return $newData ?? [];

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    //campos para mapear los campos que se utlizaran para la data
    function inputsDb($table, $data){
        if($table == 'drivers'){
            foreach($data ?? [] as $item){
                $data_db[]['name'] = $item->nombre;
            }
        }else if($table == 'empresa_details'){
            foreach($data ?? [] as $index => $item){
                $data_db[$index]['name'] = $item->nombre;
                $data_db[$index]['rfc'] = $item->rfc;
                $data_db[$index]['address'] = $item->direccion;
                $data_db[$index]['razon_social'] = $item->razon_social ?? '';
            }
        }else if($table == 'payment_methods'){
            foreach($data ?? [] as $index => $item){
                $data_db[$index]['pay_method'] = $item->c_metodopago;
                $data_db[$index]['description'] = $item->{'descripción'};
            }
        }else if($table == 'unidades_sat'){
            foreach($data ?? [] as $index => $item){
                $data_db[$index]['clave_unidad'] = $item->c_claveunidad;
                $data_db[$index]['name'] = $item->nombre;
                $data_db[$index]['description'] = $item->{'descripción'};
            }
        }else if($table == 'proveedores'){
            foreach($data ?? [] as $index => $item){
                $data_db[$index]['name'] = $item->nombre;
                $data_db[$index]['code_proveedor'] = $item->codigo_proveedor;
                $data_db[$index]['rfc'] = $item->rfc_aux;
                $data_db[$index]['phone'] = $item->tel;
                $data_db[$index]['contacto'] = $item->contacto;
                $data_db[$index]['email'] = $item->e_mail;
                $data_db[$index]['address'] = $item->direccion;
                $data_db[$index]['credit_days'] = $item->dias_credito ?? 0;
                $data_db[$index]['credit'] = $item->credito ?? 0;
                $data_db[$index]['saldo'] = $item->saldo ?? 0;
            }
        }else if($table == 'brands'){
            foreach($data ?? [] as $index => $item){
                $data_db[$index]['id'] = $item->{'record_id#'};
                $data_db[$index]['name'] = $item->linea;
                $data_db[$index]['description'] = $item->descripcion;
            }
        }else if($table == 'products'){
            foreach($data ?? [] as $index => $item){
                $data_db[$index]['code_product'] = $item->codigo_del_producto;
                $data_db[$index]['description'] = $item->descripcion;
                $data_db[$index]['barcode'] = $item->codigo_barras;
                $data_db[$index]['taxes'] = $item->impuesto; 
                $data_db[$index]['amount_taxes'] = $item->valor_impuesto;
                $data_db[$index]['unit'] = $item->unidad;
                $data_db[$index]['unit_description'] = $item->{'unidad_sat___descripción'};
                $data_db[$index]['existence'] = $item->existencia_real;
                $data_db[$index]['precio'] = $item->preciov_1 ?? 0;
                $data_db[$index]['precio_mayoreo'] = $item->preciov_3 ?? 0;
                $data_db[$index]['precio_despiece'] = $item->preciov_4 ?? 0;
                $data_db[$index]['activo'] = $item->baja;
                $data_db[$index]['comments'] = $item->notas;
                $data_db[$index]['brand_id'] = (int)$item->{'linea___record_id#'};
            }
        }else if($table == 'users'){
            foreach($data ?? [] as $index => $item){
                $data_db[$index]['name'] = $item->datos_empleado___nombre;
                $data_db[$index]['email'] = $item->id_usuario;
                $data_db[$index]['phone'] = $item->telefono;
                $data_db[$index]['password'] = strlen($item->password) !== 60 ? bcrypt($item->password) : $item->password;
            }
        }else if($table == 'branchs'){
            foreach($data ?? [] as $index => $item){
                $data_db[$index]['razon_social'] = $item->razon_social;
                $data_db[$index]['name'] = $item->nombre;
                $data_db[$index]['address'] = $item->direccion;
                $data_db[$index]['phone'] = $item->tel;
                $data_db[$index]['rfc'] = $item->rfc;
                $data_db[$index]['id'] = $item->{'código_cliente'};
                $data_db[$index]['email'] = $item->correo;
            }
        }
        return $data_db ?? [];
    }

    //rows para comparar en tabla
    function keysTable($table){
        if($table == 'drivers'){
            $data['qb'] = 'nombre';
            $data['dbExt'] = 'name';
        }else if($table == 'payment_methods'){
            $data['qb'] = 'c_metodopago';
            $data['dbExt'] = 'pay_method';
        }else if($table == 'unidades_sat'){
            $data['qb'] = 'c_claveunidad';
            $data['dbExt'] = 'clave_unidad';
        }else if($table == 'proveedores'){
            $data['qb'] = 'codigo_proveedor';
            $data['dbExt'] = 'code_proveedor';
        }else if($table == 'users'){
            $data['qb'] = 'id_usuario';
            $data['dbExt'] = 'email';
        }else if($table == 'brands'){
            $data['qb'] = 'linea';
            $data['dbExt'] = 'name';
        }else if($table == 'products'){
            $data['qb'] = 'codigo_del_producto';
            $data['dbExt'] = 'code_product';
        }else if($table == 'branchs'){
            $data['qb'] = 'código_cliente';
            $data['dbExt'] = 'id';
        }else if($table == 'roles'){
            $data['qb'] = '';
            $data['dbExt'] = 'name';
        }

        return $data;
    }

    //fucnion para hacer un reinicio a la app
    public function resetDatabase()
    {   
        if (!Auth::User()->hasRole('root')) {
            abort(403, 'No permitido');
        }

        Artisan::call('migrate:refresh', [
            '--force' => true // Necesario para ejecución sin confirmación
        ]);

        return redirect()->back()->with('status', 'Se restauró correctamente.');
    }

    //funcion para ver los logs
    public function viewLogs(){
        $logPath = storage_path('logs/laravel.log');

        if (!File::exists($logPath)) {
            dd('No hay logs registrados aún.');
        }

        $rawLines = file($logPath, FILE_IGNORE_NEW_LINES);
        $rawLines = array_reverse($rawLines); // Más recientes primero

        $logs = [];
        $current = '';

        foreach ($rawLines as $line) {
            if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line)) {
                // Nueva entrada: guarda la anterior si existe
                if ($current !== '') {
                    $logs[] = trim($current);
                }
                $current = $line;
            } else {
                // Continuación del log anterior
                $current .= ' ' . trim($line);
            }
        }

        if ($current !== '') {
            $logs[] = trim($current); // última entrada
        }

        return view('logs', ['lines' => $logs]);
    }

    //funcion para limpiar archivo de logs
    public function clearLogs()
    {
        $logPath = storage_path('logs/laravel.log');

        if (File::exists($logPath)) {
            File::put($logPath, '');
        }

        return redirect()->back()->with('success', 'Logs limpiados correctamente.');
    }
}
