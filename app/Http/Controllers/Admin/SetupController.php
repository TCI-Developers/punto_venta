<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmpresaDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Crypt, Http, Log};

class SetupController extends Controller
{
    public function index()
    {
        $empresa = EmpresaDetail::first();
        if ($empresa && !empty($empresa->matriz_token)) {
            return redirect()->route('admin.index');
        }

        return view('setup.index');
    }

    // AJAX: verifica el token y devuelve los datos de sucursal
    public function verifyToken(Request $request)
    {
        $token = trim($request->input('token', ''));
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Ingresa el token.']);
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(15)
                ->get(config('services.matriz.url') . '/api/pos/catalogo');

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido o Matriz no disponible. (HTTP ' . $response->status() . ')',
                ]);
            }

            $sucursal = $response->json('sucursal');
            if (empty($sucursal)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Respuesta inesperada de Matriz: sin datos de sucursal.',
                ]);
            }

            return response()->json(['success' => true, 'sucursal' => $sucursal]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar con Matriz: ' . $e->getMessage(),
            ]);
        }
    }

    // Guarda empresa + token y redirige a la pantalla de sincronización
    public function complete(Request $request)
    {
        $token = trim($request->input('token', ''));
        if (!$token) {
            return back()->with('error', 'El token es requerido.');
        }

        try {
            $empresa = EmpresaDetail::first() ?? new EmpresaDetail();
            $empresa->matriz_token  = Crypt::encrypt($token);
            $empresa->name          = trim($request->input('name', ''));
            $empresa->razon_social  = trim($request->input('razon_social', ''));
            $empresa->rfc           = trim($request->input('rfc', ''));
            $empresa->regimen_fiscal = trim($request->input('regimen_fiscal', ''));
            $empresa->codigo_postal = trim($request->input('codigo_postal', ''));
            $empresa->address       = trim($request->input('address', ''));
            $empresa->path_logo     = trim($request->input('path_logo', ''));
            // vigencia siempre cifrada (UserController::vigencia() espera valor cifrado)
            $empresa->vigencia      = Crypt::encrypt(trim($request->input('vigencia', '')));
            $empresa->save();
        } catch (\Throwable $e) {
            Log::error('Setup: error guardando empresa: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar los datos. Intenta de nuevo.');
        }

        return redirect()->route('setup.syncing');
    }

    // Pantalla con spinner que dispara el sync del catálogo vía JS
    public function syncing()
    {
        return view('setup.syncing');
    }
}
