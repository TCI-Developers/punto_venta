<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, Product, Brand, Customer, PaymentMethod, UnidadSat, Role, Turno, UserRole, Box, Branch, BranchUser};
use Illuminate\Support\Facades\{Auth};
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
                return redirect()->back()->withErrors('error', 'Licencia vencida.');
            }
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

            if(!Auth::User()->hasAnyRole(['root'])){
                return redirect()->route('admin.startAmountBox');
            }else{
                return redirect()->route('branch.index');
            }
        }


        
        return redirect()->back()->withErrors('error', 'Credenciales Incorrectas.');
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

    //funcion Curl QuickBase
        // function getQuickBase($db, $query, $clist){
        //     $url = "https://aortizdemontellanoarevalo.quickbase.com/db/".$db; //url a donde se consulta
        //     $userToken = "b8degy_fwjc_0_djjg8pab6ss873bfjuhnjb6vdbut";

        //     $bodyQuery = "<qdbapi> 
        //         <usertoken>".$userToken."</usertoken>
        //         <query>".$query."</query>
        //         <clist>".$clist."</clist>
        //     </qdbapi>"; //consulta para obtener los productos

        //     $ch = curl_init();
        //     curl_setopt($ch, CURLOPT_URL, $url);
        //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
        //     curl_setopt($ch, CURLOPT_POSTFIELDS,$bodyQuery);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        //     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //         'Content-Type: application/xml',
        //         'Content-Length:',
        //         'QUICKBASE-ACTION: API_DoQuery'
        //     ));
            
        //     $response = curl_exec($ch);
        //     curl_close ($ch);
        //     $record = simplexml_load_string($response);
        //     $values = array();
        //     foreach($record->record as $value){
        //         $values[] = $value;
        //     }
        //     return  $values;
    // }

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

    //funcion para obtener las clientes (No se ocupan los clientes de QUICKBASE)
    // function getCustomers(){
        //     $db = 'bqa4qy37m';
        //     $query = '';
        //     $clist = '3.6.13.14.15.16.17.18';
        //     $response = $this->getQuickBase($db, $query, $clist);
        
        //     $customer = new Customer();
        //     $customer2 = $customer->setCustomers($response);
    // }
}
