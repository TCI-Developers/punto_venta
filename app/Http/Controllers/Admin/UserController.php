<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\{Auth};

class UserController extends Controller
{

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

    //test
    public function test1(){
        $apiKey = '9801cf7775d2e35c46193df68b4f2013';
        $tituloPelicula = 'Ted'; // Título de la película que quieres buscar

        $url = 'https://api.themoviedb.org/3/search/movie?api_key=' . $apiKey . '&query=' . urlencode($tituloPelicula) . '&language=es';

        // Realizar la solicitud HTTP
        $response = file_get_contents($url);

        // Decodificar la respuesta JSON en un array asociativo
        $data = json_decode($response, true);

        // Verificar si la solicitud fue exitosa y mostrar los resultados
        if (isset($data['results']) && count($data['results']) > 0) {
            $pelicula = $data['results'][0]; // Tomar la primera película de los resultados
            echo 'Título: ' . $pelicula['title'] . '<br>';
            echo 'Fecha de lanzamiento: ' . $pelicula['release_date'] . '<br>';
            dd($pelicula);
            // y así sucesivamente...
        } else {
            echo 'No se encontraron resultados para la búsqueda.'; 
        }
    }

    public function test(){
    
        // URL a la que deseas acceder
        $url = "https://link.resilio.com/#f=T1&sz=0&t=1&s=C6BZS26HY2L4FFBAPC4Q2S6YLISAWXKY&i=CWVP44A4LH4XCRNI4AZLLNX4TOA33JANU&v=2.7&a=2";

        // Inicializa cURL
        $ch = curl_init();

        // Establece la URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // Habilita el seguimiento de redirecciones
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Habilita la opción de retornar el resultado como una cadena
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Realiza la solicitud y obtén la respuesta
        $response = curl_exec($ch);

        // Verifica si hay errores
        if(curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        // Cierra la sesión cURL
        curl_close($ch);

        // Imprime el contenido de la respuesta
        echo $response;

    }
}
