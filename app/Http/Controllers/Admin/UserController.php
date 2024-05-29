<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\{Auth};

class UserController extends Controller
{

    public function preparePost(Request $request){
        dd( $request );
    }

    //funcion login Users  
    public function loginUser(Request $request){
        
        $validatedData = $request->validate([
                'phone' => ['required', 'numeric', 'digits_between:10,12'],
                'password' => 'required|min:8',   
        ]);

        $user = $this->getUserQB($request->phone, $request->password);

        if(count($user)){
            $json = json_encode($user[0]);
            $userArray = json_decode($json, true);
           
            $exist_user = User::where('phone', $request->phone)->first();
            if(!$exist_user){
                $new_user = $this->storeUser($userArray['datos_empleado___nombre'], $userArray['id_usuario'], $userArray['telefono'], $userArray['password']);
                Auth::login($new_user);
                return redirect()->route('test');
            }else{
                Auth::login($exist_user);
                return redirect()->route('test');
            }
        }
        
        return redirect()->back()->with('error', 'Credenciales Incorrectas.');
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
        $url = "https://aortizdemontellanoarevalo.quickbase.com/db/brnx9pgfy"; //url a donde se consulta
        $userToken = "b8degy_fwjc_0_djjg8pab6ss873bfjuhnjb6vdbut";
        // $tel = "4521231211";
        // $pass = "qwertyuiop";

        $query = "<qdbapi> 
            <usertoken>".$userToken."</usertoken>
            <query>{30.EX.'".$tel."'} AND {8.EX.'".$pass."'}</query>
            <clist>28.6.30.8</clist>
        </qdbapi>"; //consulta para obtener a los cortadores

        return $response = $this->getQuickBase($url, $query);
    }

    //funcion Curl QuickBase
    function getQuickBase($URL,$bodyQuery){
        $ch = curl_init();

        // si es post
        curl_setopt($ch, CURLOPT_URL, $URL);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
        //mandamos la data
        curl_setopt($ch, CURLOPT_POSTFIELDS,$bodyQuery);
        //retornamos la respuesta
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        //definimos encabezados
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
}
