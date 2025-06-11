<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Auth};
use App\Models\{Sale, Box, Devolucion};

class BoxController extends Controller
{

    //funcion para mostrar listado de cierres de turno
    public function index(){
        return view('Admin.box.index');
    }
    
    //funcion para mostrar vista de cierre de turno
    public function turnOff()
    {      
        $user_id = Auth::User()->id;
        $box = Box::where('user_id', $user_id)->where('status', 0)->orderBy('id', 'desc')->first();
        $start_date = $box->start_date ?? null;
        $end_date = date('Y-m-d H:i:s');
        $ventas = Sale::where('user_id', $user_id)->whereBetween('updated_at', [$start_date, $end_date])->get(); 
        $ventas_cerradas = [];
        $status = 0; //no existen ventas
        if(count($ventas)){
            $con = 0;
            foreach($ventas as $item){
                if($item->status == 2){
                    $ventas_cerradas[$con] = $item;
                    $con++;
                }
            }

            if(count($ventas) == count($ventas_cerradas)){
                $status = 1;
            }else{
                $status = 2;
            }

           
        }
       
        return view('Admin.box.turn_off', ['start_amount_box' => $box->start_amount_box ?? null, 'ventas_cerradas' => $ventas_cerradas, 'status' => $status]);
    }

    //funcion para guardar el monto incial de la caja
    public function storeStarAmountBox(Request $request){
        $validatedData = $request->validate([
            'start_amount_box' => 'required'],['start_amount_box.required' => 'El monto inicial es requerido.']
        );

        $user = Auth::User();
        $box = Box::where('status', '>', 0)->orderBy('end_date', 'desc')->first();

        if(is_object($box) && !isset($request->next) && $request->next != 'on'){
            if((int)$request->start_amount_box != (int)$box->amount_cash_user){
                Auth::logout($user);
                return redirect()->route('sale.index')->with('error', 'El monto inicial no coincide con el último cierre.');
            }
        }

        $box = new Box();
        $box->user_id = $user->id;
        $box->status = 0;
        $box->start_date = date('Y-m-d H:i:s');
        $box->start_amount_box = $request->start_amount_box;
        $box->save();

        return redirect()->route('sale.index');
    }

    //funcion para guardar cierre de caja
    public function store(Request $request)
    {   
        //pruebas
        $user_id = Auth::User()->id;
        $box = Box::where('user_id', $user_id)->where('status', 0)->orderBy('id', 'desc')->first();
        $start_date = $box->start_date;
        $end_date = date('Y-m-d H:i:s');

        $total_efectivo = Sale::where('user_id', $user_id)->where('status', 2)->where('type_payment', 'efectivo')->whereBetween('updated_at', [$start_date, $end_date])->sum('total_sale'); 
        $total_tarjeta = Sale::where('user_id', $user_id)->where('status', 2)->where('type_payment', 'tarjeta')->whereBetween('updated_at', [$start_date, $end_date])->sum('total_sale'); 
        $devoluciones = Devolucion::whereBetween('updated_at', [$start_date, $end_date])->get();
        $total_devolucion_efectivo = 0;
        $total_devolucion_tarjeta = 0;

        if(count($devoluciones))
        foreach($devoluciones as $item){
            if($item->getSale->type_payment = 'efectivo'){
                $total_devolucion_efectivo += $item->total_devolucion;
            }else{
                $total_devolucion_tarjeta += $item->total_devolucion;
            }
        }
        
        $tolerancia = 1; //margen de un peso para el efectivo
        $total_efect = ($box->start_amount_box + $total_efectivo) - $total_devolucion_efectivo; //total efectivo 
        $montos = $total_efect - $request->monto_efectivo; 
        $val_tolerancia = $montos > $tolerancia;
        
       

        $totalTicketsCoins = $this->getTotalTicketsCoins($request->tickets, $request->coins);
        $rules = $this->rules($total_efect, round($total_tarjeta, 2), round($request->monto_efectivo,2), round($request->monto_tarjeta, 2), $val_tolerancia);
        $rules_tickets_coins = $this->rules_tickets_coins($total_efect, round($totalTicketsCoins,2), $val_tolerancia);

        if(!$request->acept){
            $validated = $request->validate($rules[0] ?? [], $rules[1] ?? []);
            $validate_tickets_coins = $request->validate($rules_tickets_coins);
        } 

        $box->end_date = $end_date;
        $box->amount_credit_system = round($total_tarjeta, 2);
        $box->amount_cash_system = round($total_efectivo, 2);
        $box->total_system = round(($total_tarjeta + $total_efectivo), 2);

        $box->amount_credit_user = round($request->monto_tarjeta,2);
        $box->amount_cash_user = round($request->monto_efectivo,2);
        $box->total_user = round(($request->monto_tarjeta + $request->monto_efectivo),2);

        $box->ticket_1000 = $request->tickets['1000'] ?? 0;
        $box->ticket_500 = $request->tickets['500'] ?? 0;
        $box->ticket_200 = $request->tickets['200'] ?? 0;
        $box->ticket_100 = $request->tickets['100'] ?? 0;
        $box->ticket_50 = $request->tickets['50'] ?? 0;
        $box->ticket_20 = $request->tickets['20'] ?? 0;

        $box->coin_20 = $request->coins['20'] ?? 0;
        $box->coin_10 = $request->coins['10'] ?? 0;
        $box->coin_5 = $request->coins['5'] ?? 0;
        $box->coin_2 = $request->coins['2'] ?? 0;
        $box->coin_1 = $request->coins['1'] ?? 0;
        $box->coin_50_cen = $request->coins['_50'] ?? 0;

        $totales = ($total_tarjeta + $total_efectivo) - $total_devolucion_efectivo ?? 0; //sistema
        $ingresado = $request->monto_tarjeta + ($request->monto_efectivo - $box->start_amount_box); //ingresdo empleado

        $box->status = (($totales - $ingresado) < 1) ? 1:2;
        // dd($box);
        $box->save();

        Auth::logout(Auth::User());

        return redirect()->route('login')->with('success', 'Cierre de caja con exito.');
    }

    //funcion para obtener el conteo de billetes y monedas
    function getTotalTicketsCoins($tickets, $coins){
        $total_tickes = 0; 
        $total_coins = 0;
        foreach($tickets as $denominacion => $item){            
            $total_tickes += $item != 0 ? ($denominacion * $item):0;
        }
        foreach($coins as $denominacion => $item){            
            $total_coins += $item != 0 ? (str_replace('_','.',$denominacion) * $item):0;
        }
        $total = ($total_coins + $total_tickes);
        return $total;
    }

    //reglas de validacion
    public function rules($total_efectivo, $total_tarjeta, $efectivo_manual, $tarjeta_manual, $tolerancia){
        if($tolerancia){
            if($efectivo_manual == null || $efectivo_manual >= 0){
                $arr[0] = ['monto_efectivo' => 'required|in:'.($total_efectivo).''];
                $arr[1] = ['monto_efectivo' => 'El monto que ingresaste no concuerda con lo vendido en efectivo.'];
            }
        }

        if($tarjeta_manual == null || $tarjeta_manual > 0){
            $arr[0] = ['monto_tarjeta' => 'required|in:'.$total_tarjeta.''];
            $arr[1] = ['monto_tarjeta' => 'El monto que ingresaste no concuerda con lo vendido con tarjeta.'];
        }

        return $arr ?? [];
    } 

    //funcion paa validar que el conteo de billetes y monedas concuerde con lo vendido
    public function rules_tickets_coins($total_efectivo, $total_tickets_coins, $tolerancia){
        $rules = [
            'tickets' => [
                function ($attribute, $value, $fail) use ($total_efectivo, $total_tickets_coins, $tolerancia) {
                    if ($total_tickets_coins == 0) {
                        $fail('Ingresa el conteo de billetes y monedas.');
                    }else if($tolerancia && $total_tickets_coins != ($total_efectivo)){
                        $fail('El conteo de billetes y monedas no concuerda con el total de ventas que realizaste.');
                    }
                },
            ],
        ];
       
        return $rules;
    }

}
