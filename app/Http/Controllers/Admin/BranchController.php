<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Auth};
use App\Models\{User, Branch, BranchUser, Product, Brand, Box};

class BranchController extends Controller
{   
    // index
    public function index($status = 1){      
        $user = Auth::User();
        $branchs = Branch::where('status', $status)->get();
        $users = User::get(); //validar que no salgan roles que no deben ir
        return view('Admin.branchs.index', ['branchs' => $branchs, 'users' => $users, 'status' => $status, 'user' => $user]);
    }

    //funcion mostrar vista para crear sucursal nueva
    public function create(){
        //status 0 = mostrar formulario para nueva sucursal
        $users = User::get();
        return view('Admin.branchs.show', ['users' => $users, 'status' => 0]);
    }

    // guardar sucursal
    public function store(Request $request, $branch_id = null){   
        $validated = $request->validate(['name' => 'required', 'address' => 'required'], 
        ['name' => 'El nombre es requerido.', 'address' => 'La dirección es requerida.']);
       
        $message = 'guardada';
        $branch = new Branch();
        if(!is_null($branch_id)){
            $branch = Branch::find($branch_id);
            $message = 'actualizada';
        }
        $branch->user_id = Auth::User()->id;
        $branch->name = $request->name;
        $branch->address = $request->address;
        $branch->save();

        if(isset($request->user_id) && count($request->user_id)){
            BranchUser::where('branch_id', $branch->id)->delete();
            for($i=0; $i < count($request->user_id); $i++) { 
                $branch_user = new BranchUser();
                $branch_user->branch_id = $branch->id;
                $branch_user->user_id = $request->user_id[$i];
                $branch_user->save();
            }
        }

        return redirect()->route('branchs.index')->with('success', 'Sucursal '.$message.' correctamente');
    }

    // mostrar sucursal
    public function show(string $id, $status = 1){
        $branch = Branch::find($id);
        $users = User::get();
        if(is_object($branch)){
            $users_exist_in_branch = $branch->getUsers($branch->id);
            return view('Admin.branchs.show', ['branch' => $branch, 'users' => $users, 'users_exist_in_branch' => $users_exist_in_branch, 'status' => $status]);
        }
        return redirect()->back()->with('error', 'Ocurrio un error.');
    }

    // actualizar sucursal
    public function update(Request $request, string $id){
        //
    }

    // inhabilitar sucursal
    public function destroy(string $id, $status = 0){
        $branch = Branch::find($id);
        if(is_object($branch)){
            $branch->status = $status;
            $branch->save();

            $message = $status == 0 ? 'inhabilitada':'habilitada';
            return redirect()->back()->with('info', 'Sucursal '.$message.' con exito.');
        }
        return redirect()->back()->with('error', 'Ocurrio un error.');
    }

    //funcion para importar sucursales de quickbase
    public function importarQuickbase($table_name){
        $sucursales = $this->getQuickBase($table_name);
        $message = 'No se encontraron sucursales por importar.';

        if(count($sucursales)){
            $con = 0;
            foreach($sucursales as $item){
                $branch_exist = Branch::where('razon_social', $item->razon_social)->first();
                if(!is_object($branch_exist)){
                    $branch = new Branch();
                    $branch->name = $item->nombre; 
                    $branch->address = $item->direccion; 
                    $branch->rfc = $item->rfc; 
                    $branch->phone = $item->telefono; 
                    $branch->razon_social = $item->razon_social; 
                    $branch->save();
                    $con++;
                }
            }
            if($con > 0){
                $message = $con.' Sucursales importadas con exito.';
            }

            return redirect()->back()->with('success', $message);
        }
        return redirect()->back()->with('error', $message);
    }

    //funcion para obtener todos los proudctos
    function getProducts2($branch_id){
        $response = $this->getQuickBase('brands');
        $brand = new Brand();
        $respuesta_brand = $brand->setBrands($response);

        if($respuesta_brand){
            $response = $this->getQuickBase('productos');
            
            $product = new Product();
            $respuesta_proudct = $product->setProducs($response, $branch_id); //esto me devuelve un true o false dependiendo de si logro guardar o no
             
            if($respuesta_proudct){
                return redirect()->back()->with('success', 'Importacion de productos correcta.');
            }else{
                return redirect()->back()->with('error', 'Ocurrio un error inesperado.');
            }
        }
        return redirect()->back()->with('error', 'No se encontro informacion para importar.');
    } 

    //asignamos sucursal a usuario y redireccionamos a ventas
    public function setSucursalUser($branch_id){
        $user = Auth::User();

        if($user->branch_id !== null && $user->branch_id != $branch_id && !$user->hasAnyRole(['root', 'admin'])){
            return redirect()->back()->with('error', 'No puedes cambiar de sucursal, hasta no cerrar sesión.');
        }

        $user_model = User::find($user->id);
        $user_model->branch_id = $branch_id;
        $user_model->save(); 

        // if ($this->hasInternetConnection()) {
        //     $this->getEmpresa(); 
        //     $this->getDrivers();
        //     $this->getBrands();
        //     $this->getPaymentMethods();
        //     $this->getUnidadesSat();
        //     $this->getProducts(); 
        // }

        return redirect()->route('sale.index');
    }
}
