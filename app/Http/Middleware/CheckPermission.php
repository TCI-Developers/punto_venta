<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $module, $submodule = null, $action = null): Response
    {   
        $user = Auth::user();
        // if ($user && $user->hasRole('root')) {
        //     return $next($request);
        // }

        $actions = [];
        if (!is_null($action)) {
            $decode = explode('|',$action);
            foreach($decode as $item){
                $val = str_replace('[','', $item); 
                $val = str_replace(']','', $val); 
                $actions[] = $val;
            }
        }

        if(Auth::User()->hasPermissionThroughModule($module) && is_null($submodule) && is_null($action)){
            return $next($request);
        }

        foreach ($actions as $act) {
            if (Auth::User()->hasPermissionThroughModule($module, $submodule, $act)) {
                return $next($request);
            }
        }

        return redirect()->back()->with('error', 'Acción no autorizada');
    }
}
