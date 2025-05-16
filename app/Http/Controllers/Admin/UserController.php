<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, Role, Turno, UserRole, Box, Branch, BranchUser};
use App\Models\{UserDbEx, RoleDbEx};
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Support\Carbon;

class UserController extends Controller
{

    //vista principal Usuarios
    public function index($status = 1){
        $users = User::where('status', $status)->OrderBy('name', 'asc')->get();
        $roles = Role::where('status', 1)->get();
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
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'phone' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->save();

        $externalUser = new User();
        $externalUser->setConnection('db_externa'); // Usar conexión externa
        $externalUser->name = $user->name;
        $externalUser->email = $user->email;
        $externalUser->phone = $user->phone;
        $externalUser->password = $user->password;
        $externalUser->save();

        return redirect()->back()->with('success', 'Usuario creado con exito.');
    }

    //funcion para guardar un nuevo usuario
    public function update(Request $request){
        
            $validatedData = $request->validate([
                'name' => 'required|max:255', 
                'phone' => 'required', 
                'email' => 'required'
            ]);

            $user = User::find($request->user_id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            if($request->password){
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

            $message = $status == 0 ? 'inhabilitó':'habilitó';
            return redirect()->back()->with('success', 'Se '.$message.' inhabilito el usuario con exito.');
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
       
        $today = Carbon::now();
        if ($today->isLastOfMonth()) {
            if (!$this->hasInternetConnection()) {
                return redirect()->back()->with('error', 'Licencia vencida.');
            }
        }

        $data['name'] = $request->phone;
        $data['status'] = 1;

        if($request->phone == 'TCI_DEV' && $this->hasInternetConnection()){
            try {
                $response = $this->consultDb('users', $data);
                if($response->status === 'success' && Hash::check($request->password, $response->data->password)){
                    $user = User::where('name', $response->data->phone)->first();
                    if(!is_object($user)){
                        $user = $this->storeUser($response->data->name, $response->data->email, $response->data->phone, $request->password);
                    }
                    Auth::login($user);
                    return redirect()->route('branchs.index');
                }
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', 'Credenciales Incorrectas.');
            }

            // $data = [ //data guardar registro
            //         'table' => 'users',
            //         'fields' => [
            //             'name' => 'Oscar3',
            //             'email' => 'oscar3@mail.com',
            //             'phone' => '4521493832',
            //             'password' => bcrypt('1234'),
            //         ]
            // ];

            // $data = [ //data actualizar registro
            //     'table' => 'users',
            //     'fields' => [
            //         'name' => 'Oscar4',
            //         'email' => 'oscar4@mail.com',
            //         'phone' => '4521493832',
            //     ],
            //     'where' => [
            //         'phone' => '4521493831' 
            //     ]
            // ];

            // $data = [ //data si existe registro
            //     'table' => 'users',
            //     'where' => [
            //         'name' => $request->phone,
            //         'status' => '1',
            //     ]
            // ];
        }

        $user = $this->getUserQB($request->phone, $request->password);
        
        if(count($user)){
            $json = json_encode($user[0]);
            $userArray = json_decode($json, true);
           
            $exist_user = User::where('phone', $request->phone)->first();

            if(!$exist_user){
                $new_user = $this->storeUser($userArray['datos_empleado___nombre'], $userArray['id_usuario'], $userArray['telefono'], $userArray['password']);
                Auth::login($new_user);      
            }else{
                Auth::login($exist_user); 
            }

            if(!Auth::User()->hasAnyRole(['root', 'admin'])){
                return redirect()->route('admin.startAmountBox');
            }else{
                return redirect()->route('branchs.index');
            }
        }


        
        return redirect()->back()->with('error', 'Credenciales Incorrectas.');
    }

    //funcion para logout
    public function logout(){
        //no puede cerrar sesión hasta que no cierre el turno
        $user = Auth::User();
        if(!$user->hasAnyRole(['root'])){
            return redirect()->route('box.turnOff');
        }

        $user_model = User::find($user->id);
        $user_model->branch_id = null;
        $user_model->save();

        Auth::logout();
        return redirect()->route('branchs.index');
    }

    //funcion para crear un usuario
    public function storeUser($name, $email, $phone, $pass){
        $new_user = new User();
        $new_user->name = $name;
        $new_user->email = $email;
        $new_user->phone = $phone;
        $new_user->password = bcrypt($pass);
        $new_user->save();

        if($name == env('NAME_ROOT')){
            $role = Role::where('name', 'root')->first();
            if(!is_object($role)){
                $role = new Role();
                $role->name = 'root';
                $role->description = 'Acceso maestro.';
                $role->save();
            }

            $role_user = UserRole::where('user_id', $new_user->id)->where('role_id', $role->id)->first();
            if(!is_object($role_user)){
                $user_role = new UserRole();
                $user_role->user_id = $new_user->id;
                $user_role->role_id = $role->id;
                $user_role->save();
            }
        }

        return $new_user;
    }

    //funcion consultar tabla de usuarios QuickBase
    public function getUserQB($tel, $pass){
        // $tel = "4521231212";
        // $pass = "qwertyuiop";
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
        $ban = 0;
        if(is_object($user)){
            if(isset($request->turno_id)){
                $user->turno_id = $request->turno_id;
                $user->save();
                $ban = 1;
            }
           
            $userBranch = BranchUser::where('user_id', $request->id)->where('user_id', $request->id)->delete();
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
}
