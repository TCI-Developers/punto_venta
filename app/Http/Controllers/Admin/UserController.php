<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, Role, Turno, UserRole, Branch, BranchUser, EmpresaDetail, Product};
use Illuminate\Support\Facades\{Auth, Hash, Crypt, DB, Log};

class UserController extends Controller
{

    //vista principal Usuarios
    public function index($status = 1){
        $user_auth = Auth::User()->hasRole('root');
        if($user_auth){
            $users = User::where('status', $status)->OrderBy('name', 'asc')->get();
            $roles = Role::where('status', 1)->get();
        }else{
            $users = User::where('status', $status)->where('name', '!=','TCI_DEV')->OrderBy('name', 'asc')->get();
            $roles = Role::where('status', 1)->where('name', '!=', 'root')->get();
        }

        $turnos = Turno::where('status', 1)->get();
        $branchs = Branch::where('status', 1)->get();

        $user_branch = [];
        if(count($users)){
            foreach($users as $user){
                $user_branch[$user->id] = $user->getBranchs;

            }
        }
        return view('Admin.users.index', ['users' => $users, 'roles' => $roles, 'turnos' => $turnos, 'status' => $status, 'branchs' => $branchs, 'user_branch' => $user_branch]);
    }

    //funcion para guardar un nuevo usuario
    public function store(Request $request){
        $request->validate([
            'name'          => 'required|max:255',
            'phone'         => 'required',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required',
            'confirmedPass' => 'required|same:password',
        ], [
            'email.unique'          => 'El email ya está en uso.',
            'email.email'           => 'El email no tiene un formato válido.',
            'confirmedPass.same'    => 'Las contraseñas no coinciden.',
            'confirmedPass.required'=> 'La confirmación de contraseña es requerida.',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Usuario creado con exito.');
    }

    //funcion para guardar un nuevo usuario
    public function update(Request $request){
        $request->validate([
            'name'  => 'required|max:255',
            'phone' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::find($request->user_id);
        if(!is_object($user)){
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if($request->password){
            $request->validate(['confirmedPass' => 'required|same:password'], [
                'confirmedPass.same'    => 'Las contraseñas no coinciden.',
                'confirmedPass.required'=> 'La confirmación de contraseña es requerida.',
            ]);
            $user->password = bcrypt($request->password);
        }
        $user->save();

        return redirect()->back()->with('success', 'Usuario actualizado con exito.');
    }

    //funcion para inhabilitar un usuario
    public function destroy($id, $status){
        $user = User::find($id);
        if(is_object($user)){
            $user->status = $status;
            $user->save();

            $message = $status == 0 ? 'inhabilitó' : 'habilitó';
            return redirect()->back()->with('success', 'Se '.$message.' el usuario con exito.');
        }
        return redirect()->back()->with('error', 'Ocurrio un error.');
    }

    //funcion login Users  
    public function loginUser(Request $request){
        $validatedData = $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ], [
            'phone.required' => 'El teléfono es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        
        $supportUser = config('services.support.name_root');
        if($supportUser && $request->phone == $supportUser && $this->hasInternetConnection()){
            $data['name'] = $request->phone;
            $data['status'] = 1;

            try {
                $response = $this->consultDb('users', $data);

                if($response->status === 'success' && Hash::check($request->password, $response->data[0]->password)){
                    $user = User::where('name', $response->data[0]->name)->first();
                    if(!is_object($user)){
                        $user = $this->storeUser($response->data[0]->name, $response->data[0]->email, $response->data[0]->phone, $request->password);
                    }

                    Auth::login($user);
                    return redirect()->route('branchs.index');
                } 
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', 'Credenciales Incorrectas.');
            }
        }

        $user_local = User::where('phone', $request->phone)->where('status', 1)->first();

        if(!is_object($user_local) || !Hash::check($request->password, $user_local->password)){
            return redirect()->back()->with('error', 'Credenciales Incorrectas.');
        }

        if($this->vigencia() && !$user_local->hasRole('root')){
            return redirect()->back()->with('error', 'Licencia vencida, contacta al proveedor.');
        }

        Auth::login($user_local);

        $this->syncProductPrices();

        if(!Auth::User()->hasAnyRole(['root', 'admin'])){
            return redirect()->route('admin.startAmountBox');
        }

        return redirect()->route('sale.index');
    }

    //trae de DB Externa los precios vigentes y actualiza los productos que ya existen localmente.
    //no crea productos nuevos (eso lo hace la importación de Quick) y nunca debe tronar el login:
    //sin internet, o si el servicio externo falla, simplemente se omite en silencio.
    //trae precios vigentes del catalogo de Matriz y actualiza los productos que ya existen
    //localmente. Antes hacia un UPDATE individual por producto (con el catalogo ya sincronizado
    //son miles), lo que hacia lento cada login -- ahora se actualiza en lotes con una sola
    //sentencia SQL (CASE WHEN) por cada 500 productos.
    private function syncProductPrices(){
        try {
            if(!$this->hasInternetConnection()){
                return;
            }

            $response = $this->matrizApi('get', 'catalogo');
            if(!$response->successful()){
                return;
            }

            $productos = $response->json('productos') ?? [];
            if(!count($productos)){
                return;
            }

            DB::transaction(function () use ($productos) {
                foreach(array_chunk($productos, 500) as $chunk){
                    $codes = [];
                    $precioWhens = [];
                    $mayoreoWhens = [];
                    $cantidadMayoreoWhens = [];
                    $despiezoWhens = [];
                    $precioBindings = [];
                    $mayoreoBindings = [];
                    $cantidadMayoreoBindings = [];
                    $despiezoBindings = [];

                    foreach($chunk as $item){
                        $code = trim((string)($item['code_product'] ?? ''));
                        if($code === ''){
                            continue;
                        }

                        $codes[] = $code;

                        $precioWhens[] = 'WHEN ? THEN ?';
                        $precioBindings[] = $code;
                        $precioBindings[] = (float)($item['precio'] ?? 0);

                        $mayoreoWhens[] = 'WHEN ? THEN ?';
                        $mayoreoBindings[] = $code;
                        $mayoreoBindings[] = (float)($item['precio_mayoreo'] ?? 0);

                        $cantidadMayoreoWhens[] = 'WHEN ? THEN ?';
                        $cantidadMayoreoBindings[] = $code;
                        $cantidadMayoreoBindings[] = (float)($item['cantidad_mayoreo'] ?? 0);

                        $despiezoWhens[] = 'WHEN ? THEN ?';
                        $despiezoBindings[] = $code;
                        $despiezoBindings[] = (float)($item['precio_despiece'] ?? 0);
                    }

                    if(!count($codes)){
                        continue;
                    }

                    $placeholders = implode(',', array_fill(0, count($codes), '?'));

                    $sql = "UPDATE products SET
                        precio = CASE code_product " . implode(' ', $precioWhens) . " ELSE precio END,
                        precio_mayoreo = CASE code_product " . implode(' ', $mayoreoWhens) . " ELSE precio_mayoreo END,
                        cantidad_mayoreo = CASE code_product " . implode(' ', $cantidadMayoreoWhens) . " ELSE cantidad_mayoreo END,
                        precio_despiece = CASE code_product " . implode(' ', $despiezoWhens) . " ELSE precio_despiece END
                        WHERE code_product IN ({$placeholders})";

                    $bindings = array_merge($precioBindings, $mayoreoBindings, $cantidadMayoreoBindings, $despiezoBindings, $codes);

                    DB::update($sql, $bindings);

                    // las presentaciones (parts_to_product) tienen su propio precio
                    // independiente -- sin este paso se quedan con el precio viejo aunque el
                    // producto base ya se haya actualizado (asi se detecto el desfase: 244
                    // productos con precio de presentacion desincronizado del precio real).
                    $this->cascadePresentationPrices($codes);
                }
            });
        } catch (\Throwable $th) {
            Log::warning('No se pudieron actualizar los precios de productos al iniciar sesión: '.$th->getMessage());
        }
    }

    //funcion para logout
    public function logout(){
        //no puede cerrar sesión hasta que no cierre el turno
        // $user = Auth::User();
        // if(!$user->hasAnyRole(['root'])){
        //     return redirect()->route('box.turnOff');
        // }

        // $user_model = User::find($user->id);
        // $user_model->branch_id = null;
        // $user_model->save();

        Auth::logout();
        return redirect()->route('login');
    }

    //funcion para crear un usuario
    public function storeUser($name, $email, $phone, $pass){
        $new_user = new User();
        $new_user->name = $name;
        $new_user->email = $email;
        $new_user->phone = $phone;
        $new_user->password = bcrypt($pass);
        $new_user->save();

        return $new_user;
    }

    //funcion consultar tabla de usuarios QuickBase
    public function getUserQB($tel, $pass){
        $data['tel'] = $tel;
        $data['pass'] = $pass;
        $db = 'brnx9pgfy';
        $query = "{30.EX.'".$tel."'} AND {8.EX.'".$pass."'}";
        $clist = '28.6.30.8';

        $response = $this->getQuickBase('usuarios', $data);

        return $response;
        // return $response = $this->getQuickBase($db, $query, $clist);
    }

    //funcion para asignar roles y turno
    public function rolesTurnos(Request $request){
        $user = User::find($request->id);
        if(!is_object($user)){
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }
        $ban = 0;
        if(is_object($user)){
            if(isset($request->turno_id)){
                $user->turno_id = $request->turno_id;
                $user->save();
                $ban = 1;
            }
           
            BranchUser::where('user_id', $request->id)->delete();
            if(isset($request->branch_id)){
                for ($i=0; $i <count($request->branch_id) ; $i++) { 
                    $user_branch = new BranchUser();
                    $user_branch->user_id = $request->id;
                    $user_branch->branch_id = $request->branch_id[$i];
                    $user_branch->save();
                }
                $ban = 1;
            }

            $userRoles = UserRole::where('user_id',$request->id)->delete();
            if(isset($request->role_id) && count($request->role_id)){
                for ($i=0; $i <count($request->role_id) ; $i++) { 
                    $user_role = new UserRole();
                    $user_role->user_id = $request->id;
                    $user_role->role_id = $request->role_id[$i];
                    $user_role->save();
                }
                $ban = 1;
            }

            $icon = 'info';
            $message = 'No se asignaron roles o turnos o sucursales.';
            if($ban>0){
                $icon = 'success';
                $message = 'Selecciones asginadas con exito.';
            }

            return redirect()->back()->with($icon, $message);
        }
    }

    //alias de rolesTurnos para la ruta de actualización
    public function updateRolesTurnos(Request $request){
        return $this->rolesTurnos($request);
    }

    //funcion para validar si aun se tiene acceso al punto de venta. Antes consultaba el puente
    //legado (tciconsultoria.com), pero nada escribia una vigencia nueva ahi -- se reemplazo por
    //la Matriz, que ya trae el token por sucursal y de verdad puede actualizarse de forma remota.
    function vigencia(){
        $empresa_local = EmpresaDetail::first();
        if(is_object($empresa_local)){
            if($this->hasInternetConnection()){
                try {
                    $response = $this->matrizApi('get', 'vigencia');
                    $remoteVigencia = $response->successful() ? $response->json('vigencia') : null;

                    if($remoteVigencia){
                        $localDate = Crypt::decrypt($empresa_local->vigencia);

                        if($remoteVigencia > $localDate){
                            $empresa_local->vigencia = Crypt::encrypt($remoteVigencia);
                            $empresa_local->save();
                        }
                    }
                } catch (\Throwable $e) {
                    // Si no se pudo consultar la Matriz o descifrar la vigencia local, se conserva la local
                }
            }

            $date = Date('Y-m-d');
            try {
                $aux = Crypt::decrypt($empresa_local->vigencia);
            } catch (\Throwable $e) {
                // vigencia guardada sin cifrar o corrupta (ej. instalacion nueva importada de
                // QuickBase antes de este fix) -- no debe tronar el login, se deja pasar y se
                // registra para corregirla luego desde la pantalla de Empresa.
                Log::warning('No se pudo descifrar vigencia local en empresa_details, se omite el bloqueo de acceso: '.$e->getMessage());
                return false;
            }
            if($date > $aux){
                return true;
            }
        }
        return false;
    }
}
