<?php

namespace App\Http\Middleware;

use App\Models\EmpresaDetail;
use Closure;
use Illuminate\Http\Request;

class CheckSetup
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('setup') || $request->is('setup/*')) {
            return $next($request);
        }

        $empresa = EmpresaDetail::first();
        if (!$empresa || empty($empresa->matriz_token)) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
