<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Role, UserRole, Customer, Brand, Product, Proveedor, User, BranchUser, EmpresaDetail};
use Illuminate\Support\Facades\{Auth, Hash, Artisan, File, Http, Log, DB};
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
            // Productos tiene su propio flujo: a diferencia de las demas tablas, aqui si nos
            // interesa actualizar el precio de lo que ya existe en DB Externa (Quick es la
            // fuente real de precios), no solo insertar lo nuevo.
            if($table == 'products'){
                return $this->syncProductsToDbExterna();
            }

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
            Log::error("Error al importar {$table} de Quick a DB Externa: ".$th->getMessage());
            return redirect()->back()->with('error', 'La importación no se pudo completar.');
        }
    }

    //trae productos de Quick: a los que ya existen en DB Externa se les actualiza el precio,
    //a los nuevos se les inserta. Comparacion por code_product como texto -- addNewDataDB()
    //castea a (int), lo cual falla para codigos alfanumericos (el cast los vuelve todos 0 y
    //hacen match entre si por accidente). Se deja addNewDataDB() intacto para no afectar a las
    //demas tablas que la siguen usando.
    private function syncProductsToDbExterna(){
        ini_set('max_execution_time', 1200);

        try {
            $data_exist = $this->existDataDb('products');
            $quickBaseProducts = $this->getQuickBase('products');

            $existingCodes = [];
            if(isset($data_exist->status) && $data_exist->status){
                $dataExterna = $this->consultDb('products', '');
                $existingCodes = array_column($dataExterna->data ?? [], 'code_product');
            }

            $newItems = [];
            $updated = 0;
            $failed = 0;

            foreach($quickBaseProducts ?? [] as $item){
                $code = $item->codigo_del_producto ?? null;
                if(!$code){
                    continue;
                }

                if(in_array((string)$code, $existingCodes, true)){
                    try {
                        $this->updateDb('products', [
                            'precio' => $item->preciov_1 ?? 0,
                            'precio_mayoreo' => $item->preciov_3 ?? 0,
                            'precio_despiece' => $item->preciov_4 ?? 0,
                        ], ['code_product' => $code]);
                        $updated++;
                    } catch (\Throwable $th) {
                        $failed++;
                    }
                } else {
                    $newItems[] = $item;
                }
            }

            if(count($newItems)){
                $data_db = $this->inputsDb('products', $newItems);
                $this->saveDb('products', $data_db);
            }

            $message = "{$updated} precios actualizados, ".count($newItems)." productos nuevos.";
            if($failed > 0){
                $message .= " {$failed} fallaron al actualizar.";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Throwable $th) {
            Log::error('Error al sincronizar productos de Quick a DB Externa: '.$th->getMessage());
            return redirect()->back()->with('error', 'La importación no se pudo completar.');
        }
    }

    //funcion para importar el catalogo completo (lineas/productos/proveedores/clientes) directo
    //desde la Matriz, en un solo paso (reemplaza a Quick -> DB Externa -> Local para estas 4 tablas).
    //este boton (pantalla de Importacion) siempre hace un sync COMPLETO (sin updated_after),
    //a diferencia del banner de "hay cambios" que usa el incremental -- se deja asi a proposito
    //como opcion de "resincronizar todo desde cero" ante cualquier duda/desfase.
    public function importCatalogoFromMatriz(){
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');

        $result = $this->runCatalogSync(null);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        $this->markCatalogSynced();

        return redirect()->back()->with('success', 'Catálogo sincronizado desde Matriz.');
    }

    //version del mismo sync pensada para el banner de "hay cambios en el catalogo" -- via
    //fetch/AJAX para no sacar al usuario de la pantalla en la que este, e incremental
    //(updated_after = ultimo sync aplicado) para que sea rapida.
    public function catalogSyncAjax(){
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');

        $empresa = EmpresaDetail::first();
        $updatedAfter = $empresa?->last_catalog_sync?->toIso8601String();

        $result = $this->runCatalogSync($updatedAfter);

        if ($result['success']) {
            $this->markCatalogSynced();
        }

        return response()->json($result);
    }

    //funcion consultada por polling desde el navegador (sin aplicar nada) para saber si hay
    //cambios pendientes en el catalogo de la Matriz desde la ultima sincronizacion aplicada.
    //siempre falla en silencio (pending=false) -- nunca debe romper la pantalla por esto.
    public function catalogStatus(){
        try {
            $empresa = EmpresaDetail::first();
            if (!$empresa) {
                return response()->json(['pending' => false]);
            }

            $updatedAfter = $empresa->last_catalog_sync?->toIso8601String();
            $params = $updatedAfter ? ['updated_after' => $updatedAfter] : [];
            $response = $this->matrizApi('get', 'catalogo', $params);

            if (!$response->successful()) {
                return response()->json(['pending' => false]);
            }

            $counts = $this->countCatalogPayload($response->json());

            // 'usuarios' no respeta updated_after del lado de la Matriz (confirmado probando
            // con una fecha limite a futuro -- las otras 4 llaves si regresan vacio, esta no).
            // Se excluye del conteo para decidir si hay "cambios pendientes": de lo contrario
            // el banner nunca se apagaria, porque esa llave siempre trae la lista completa.
            $total = $counts['productos'] + $counts['lineas'] + $counts['proveedores'] + $counts['clientes'];

            return response()->json(['pending' => $total > 0, 'counts' => $counts, 'total' => $total]);
        } catch (\Throwable $th) {
            Log::warning('No se pudo revisar cambios de catalogo en Matriz: '.$th->getMessage());
            return response()->json(['pending' => false]);
        }
    }

    //marca el momento del ultimo sync de catalogo APLICADO (no solo revisado), para que la
    //siguiente consulta (banner o boton) solo pida lo que cambio desde entonces.
    private function markCatalogSynced(): void
    {
        $empresa = EmpresaDetail::first();
        if ($empresa) {
            $empresa->last_catalog_sync = now();
            $empresa->save();
        }
    }

    //cuenta cuantos registros trae cada llave del catalogo (para el banner y el chequeo previo)
    private function countCatalogPayload(array $data): array
    {
        return [
            'productos' => count($data['productos'] ?? []),
            'lineas' => count($data['lineas'] ?? []),
            'proveedores' => count($data['proveedores'] ?? []),
            'clientes' => count($data['clientes'] ?? []),
            'usuarios' => count($data['usuarios'] ?? []),
        ];
    }

    //logica compartida de aplicar el catalogo de Matriz a la BD local. $updatedAfter = null
    //significa sync completo (usado por el boton de Importacion); con fecha, es incremental
    //(usado por el banner) -- ver nota sobre la revocacion mas abajo, es el unico paso que
    //se comporta distinto entre los dos modos.
    private function runCatalogSync(?string $updatedAfter): array
    {
        $params = $updatedAfter ? ['updated_after' => $updatedAfter] : [];
        $response = $this->matrizApi('get', 'catalogo', $params);

        if (!$response->successful()) {
            return ['success' => false, 'message' => 'No se pudo conectar con la Matriz.'];
        }

        $data = $response->json();

        try {
            DB::transaction(function () use ($data, $updatedAfter) {
                // Lineas primero: productos referencia linea_id como brand_id, tiene que existir
                // la marca antes de asignarsela a un producto (brand_id es FK obligatoria).
                foreach ($data['lineas'] ?? [] as $l) {
                    Brand::updateOrCreate(
                        ['id' => $l['id']],
                        [
                            'name' => $l['codigo'],
                            'description' => $l['descripcion'],
                        ]
                    );
                }

                $productosOmitidos = [];
                $codigosActualizados = [];
                foreach ($data['productos'] ?? [] as $p) {
                    // brand_id es NOT NULL localmente -- si Matriz manda un producto sin linea_id
                    // asignada, se omite ese producto en vez de tronar TODA la transaccion (con
                    // miles de productos por sync, un solo registro con datos incompletos no
                    // debe bloquear el resto).
                    if (empty($p['linea_id'])) {
                        $productosOmitidos[] = $p['code_product'] ?? '(sin code_product)';
                        continue;
                    }

                    Product::updateOrCreate(
                        ['code_product' => $p['code_product']],
                        [
                            'description' => $p['description'],
                            'barcode' => $p['barcode'],
                            'unit' => $p['unit'],
                            'unit_description' => $p['unit_description'],
                            'clave_sat' => $p['clave_sat'] ?? null,
                            'taxes' => $p['taxes'],
                            'amount_taxes' => $p['amount_taxes'],
                            'precio' => $p['precio'],
                            'precio_mayoreo' => $p['precio_mayoreo'],
                            'cantidad_mayoreo' => $p['cantidad_mayoreo'] ?? 0,
                            'precio_despiece' => $p['precio_despiece'],
                            'brand_id' => $p['linea_id'],
                            'category_id' => $p['category_id'] ?? null,
                            'activo' => true,
                        ]
                    );
                    $codigosActualizados[] = $p['code_product'];
                }
                if (count($productosOmitidos)) {
                    Log::warning('Productos omitidos en sync de catalogo Matriz por no tener linea_id: '.implode(', ', $productosOmitidos));
                }

                // mismo hueco que en syncProductPrices(): sin esto, este camino (boton/banner de
                // catalogo) actualiza el producto pero deja las presentaciones con el precio viejo.
                $this->cascadePresentationPrices($codigosActualizados);

                // proveedores/clientes: se usa el id de Matriz para el match, no rfc/code_proveedor
                // (son nullable localmente -- dos registros sin ese dato se pisarian entre si).
                foreach ($data['proveedores'] ?? [] as $p) {
                    Proveedor::updateOrCreate(
                        ['id' => $p['id']],
                        [
                            'code_proveedor' => $p['code_proveedor'],
                            'name' => $p['name'],
                            'rfc' => $p['rfc'],
                            'phone' => $p['phone'],
                            'contacto' => $p['contacto'],
                            'email' => $p['email'],
                        ]
                    );
                }

                foreach ($data['clientes'] ?? [] as $c) {
                    Customer::updateOrCreate(
                        ['id' => $c['id']],
                        [
                            'name' => $c['nombre'],
                            'razon_social' => $c['razon_social'],
                            'rfc' => $c['rfc'],
                            'regimen_fiscal' => $c['regimen_fiscal'],
                        ]
                    );
                }

                // usuarios: Matriz solo gestiona la relacion usuario-sucursal, no crea usuarios.
                // Si el usuario todavia no existe localmente (no ha venido de QuickBase), se
                // omite -- crearlo aqui sin contraseña haria que el import de QB (insertar-solo)
                // lo saltara despues y se quedara sin poder loguearse nunca.
                // Las filas se marcan source='matriz' para poder revocarlas despues sin tocar
                // accesos asignados a mano en las pantallas de Usuarios/Sucursales de POSTCI.
                $emailsEnMatriz = [];
                foreach ($data['usuarios'] ?? [] as $u) {
                    $email = $u['email'] ?? null;
                    if($email){
                        $emailsEnMatriz[] = $email;
                    }

                    $user = User::where('email', $email)->first();
                    if (!$user) {
                        continue;
                    }

                    BranchUser::where('user_id', $user->id)->where('source', 'matriz')->delete();
                    foreach ($u['sucursal_ids'] ?? [] as $branchId) {
                        $branch_user = new BranchUser();
                        $branch_user->user_id = $user->id;
                        $branch_user->branch_id = $branchId;
                        $branch_user->source = 'matriz';
                        $branch_user->save();
                    }
                }

                // revocacion: usuarios que en algun sync anterior tenian acceso via Matriz pero
                // ya no vienen en el catalogo actual (les quitaron todas las sucursales alla)
                // pierden ese acceso. No afecta accesos con source distinto de 'matriz'.
                //
                // OJO: esto solo es correcto en un sync COMPLETO. En uno incremental
                // (updated_after != null), $emailsEnMatriz solo trae los usuarios que
                // cambiaron recientemente -- la gran mayoria de usuarios no vienen ahi
                // simplemente porque no cambiaron, no porque perdieron acceso. Si se corriera
                // esta revocacion tambien en modo incremental, se le quitaria el acceso a
                // practicamente todos los usuarios en cada sync parcial. Por eso se salta
                // por completo cuando $updatedAfter tiene valor -- la revocacion "usuario ya
                // no existe en absoluto en Matriz" solo se aplica en el resync completo del
                // boton de Importacion. La revocacion de un usuario puntual (le quitaron SUS
                // sucursales) si funciona igual en ambos modos, porque esa se resuelve arriba
                // reemplazando las filas de cualquier usuario que SI aparezca en la respuesta.
                if ($updatedAfter === null) {
                    $userIdsConAccesoMatriz = BranchUser::where('source', 'matriz')->pluck('user_id')->unique();
                    foreach ($userIdsConAccesoMatriz as $userId) {
                        $user = User::find($userId);
                        if ($user && !in_array($user->email, $emailsEnMatriz, true)) {
                            BranchUser::where('user_id', $userId)->where('source', 'matriz')->delete();
                        }
                    }
                }
            });
        } catch (\Throwable $th) {
            Log::error('Error al importar catalogo desde Matriz: '.$th->getMessage());
            return ['success' => false, 'message' => 'La importación no se pudo completar.'];
        }

        return ['success' => true, 'counts' => $this->countCatalogPayload($data)];
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
            $created = 0;
            $skipped = 0;

            foreach($data as $item){
                $attributes = (array)$item;

                // DB Externa trae, para algunos productos viejos, brand_id=0 (invalido) junto con
                // un linea_id que si tiene la referencia real de marca. Usamos linea_id como
                // respaldo y validamos que la marca exista localmente antes de insertar, ya que
                // brand_id tiene llave foranea obligatoria hacia brands.
                if($table == 'products'){
                    $brandId = (int)($attributes['brand_id'] ?? 0);
                    if($brandId <= 0){
                        $brandId = (int)($attributes['linea_id'] ?? 0);
                    }

                    if($brandId <= 0 || !Brand::find($brandId)){
                        $skipped++;
                        Log::warning("Producto omitido por marca invalida/inexistente.", ['item' => $attributes]);
                        continue;
                    }

                    $attributes['brand_id'] = $brandId;
                }

                try {
                    $model::create($attributes);
                    $created++;
                } catch (\Throwable $th) {
                    $skipped++;
                    Log::warning("No se pudo importar un registro de {$table}: ".$th->getMessage(), ['item' => $attributes]);
                }
            }

            $message = "{$created} registros importados.";
            if($skipped > 0){
                $message .= " {$skipped} omitidos (revisar log).";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Throwable $th) {
            Log::error("Error al importar {$table} de DB Externa a Local: ".$th->getMessage());
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
                
                $cliente = Customer::where('name', 'Publico General')->first();
                if(!is_object($cliente)){
                    $cliente = new Customer();
                    $cliente->name = 'Publico General';
                    $cliente->save();
                }

                $rol = Role::where('name', 'root')->first();
                if(!is_object($rol)){
                    return redirect()->back()->with('error', 'El rol root no existe. Importa los roles primero.');
                }
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

                $response = Http::get(config('services.assets.url_logo'));
                if ($response->successful()) {
                    File::put($path_logo, $response->body());
                }

                $response = Http::get(config('services.assets.url_pdf'));
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
            foreach($data ?? [] as $index => $item){
                $data_db[$index]['id'] = $item->{'record_id#'};
                $data_db[$index]['name'] = $item->nombre;
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
                $data_db[$index]['existence'] = 0;
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
            return view('logs', ['lines' => []]);
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
