<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Customer, EmpresaDetail, PaymentMethod, Role, UnidadSat, User};
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Crypt, DB, Http, Log};

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
            // Upsert la sucursal en la tabla branchs para que branch_id sea válido.
            // Se usa DB::table para respetar el id exacto de Matriz (id no está en $fillable de Branch).
            $branchId = (int) $request->input('branch_id', 0);
            if ($branchId) {
                $branchData = [
                    'name'         => trim($request->input('name', '')),
                    'razon_social' => trim($request->input('razon_social', '')),
                    'rfc'          => trim($request->input('rfc', '')),
                    'address'      => trim($request->input('address', '')),
                ];
                $exists = DB::table('branchs')->where('id', $branchId)->exists();
                if ($exists) {
                    DB::table('branchs')->where('id', $branchId)->update($branchData);
                } else {
                    DB::table('branchs')->insert(array_merge(['id' => $branchId], $branchData));
                }
            }

            $empresa = EmpresaDetail::first() ?? new EmpresaDetail();
            $empresa->matriz_token   = Crypt::encrypt($token);
            $empresa->name           = trim($request->input('name', ''));
            $empresa->razon_social   = trim($request->input('razon_social', ''));
            $empresa->rfc            = trim($request->input('rfc', ''));
            $empresa->regimen_fiscal = trim($request->input('regimen_fiscal', ''));
            $empresa->codigo_postal  = trim($request->input('codigo_postal', ''));
            $empresa->address        = trim($request->input('address', ''));
            $empresa->path_logo      = trim($request->input('path_logo', ''));
            // vigencia siempre cifrada (UserController::vigencia() espera valor cifrado)
            $empresa->vigencia       = Crypt::encrypt(trim($request->input('vigencia', '')));
            if ($branchId) {
                $empresa->branch_id = $branchId;
            }
            $empresa->save();
        } catch (\Throwable $e) {
            Log::error('Setup: error guardando empresa: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }

        return redirect()->route('setup.syncing');
    }

    // Pantalla con spinner que dispara el sync del catálogo vía JS
    public function syncing()
    {
        return view('setup.syncing');
    }

    // ── Pasos de importación (AJAX, retornan JSON) ────────────────────────────

    public function syncPaymentMethods()
    {
        try {
            // El sistema usa códigos de método_pago SAT (PUE/PPD), no forma_pago.
            // PUE = una sola exhibición (efectivo/contado), PPD = parcialidades/diferido (tarjeta/crédito).
            $defaults = [
                ['pay_method' => 'PUE', 'description' => 'Pago en una sola exhibición'],
                ['pay_method' => 'PPD', 'description' => 'Pago en parcialidades o diferido'],
            ];

            foreach ($defaults as $d) {
                PaymentMethod::firstOrCreate(['pay_method' => $d['pay_method']], $d);
            }

            return response()->json(['success' => true, 'message' => 'Métodos de pago configurados (PUE/PPD).', 'count' => PaymentMethod::count()]);
        } catch (\Throwable $e) {
            Log::error('Setup syncPaymentMethods: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function syncUnidades()
    {
        try {
            if (UnidadSat::exists()) {
                return response()->json(['success' => true, 'message' => 'Ya existían unidades SAT.', 'count' => UnidadSat::count()]);
            }

            $defaults = [
                ['clave_unidad' => 'H87', 'name' => 'Pieza',                   'description' => 'Pieza'],
                ['clave_unidad' => 'KGM', 'name' => 'Kilogramo',               'description' => 'Kilogramo'],
                ['clave_unidad' => 'GRM', 'name' => 'Gramo',                   'description' => 'Gramo'],
                ['clave_unidad' => 'LTR', 'name' => 'Litro',                   'description' => 'Litro'],
                ['clave_unidad' => 'MLT', 'name' => 'Mililitro',               'description' => 'Mililitro'],
                ['clave_unidad' => 'MTR', 'name' => 'Metro',                   'description' => 'Metro'],
                ['clave_unidad' => 'BX',  'name' => 'Caja',                    'description' => 'Caja'],
                ['clave_unidad' => 'XBX', 'name' => 'Caja (mercancías)',       'description' => 'Caja de mercancías'],
                ['clave_unidad' => 'C62', 'name' => 'Unidad',                  'description' => 'Unidad de conteo'],
                ['clave_unidad' => 'SET', 'name' => 'Juego',                   'description' => 'Juego o conjunto'],
                ['clave_unidad' => 'PR',  'name' => 'Par',                     'description' => 'Par'],
                ['clave_unidad' => 'DZN', 'name' => 'Docena',                  'description' => 'Docena'],
                ['clave_unidad' => 'MGM', 'name' => 'Miligramo',               'description' => 'Miligramo'],
                ['clave_unidad' => 'E48', 'name' => 'Unidad de servicio',      'description' => 'Unidad de servicio'],
            ];

            foreach ($defaults as $d) {
                UnidadSat::firstOrCreate(['clave_unidad' => $d['clave_unidad']], $d);
            }

            return response()->json(['success' => true, 'message' => count($defaults) . ' unidades SAT importadas.', 'count' => count($defaults)]);
        } catch (\Throwable $e) {
            Log::error('Setup syncUnidades: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function syncDrivers()
    {
        // Choferes se agregan manualmente desde el panel de administración.
        // No hay fuente de importación disponible en este punto del wizard.
        return response()->json([
            'success' => true,
            'message' => 'Se configura manualmente desde Administración.',
            'count'   => 0,
        ]);
    }

    public function createClienteGeneral()
    {
        try {
            $exists = Customer::where('name', 'Cliente General')->exists();
            if ($exists) {
                return response()->json(['success' => true, 'message' => 'El cliente general ya existe.']);
            }

            Customer::create([
                'name'           => 'Cliente General',
                'razon_social'   => 'PUBLICO EN GENERAL',
                'rfc'            => 'XAXX010101000',
                'postal_code'    => '00000',
                'regimen_fiscal' => '616',
                'status'         => 1,
            ]);

            return response()->json(['success' => true, 'message' => 'Cliente general creado correctamente.']);
        } catch (\Throwable $e) {
            Log::error('Setup createClienteGeneral: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function configureSystem()
    {
        try {
            // Crear rol root si no existe
            $rootRole = Role::firstOrCreate(
                ['name' => 'root'],
                ['description' => 'Administrador TCI']
            );

            // Asignar root a TCI_DEV
            $tciDev = User::where('name', 'TCI_DEV')->first();
            if ($tciDev) {
                $hasRole = DB::table('role_user')
                    ->where('user_id', $tciDev->id)
                    ->where('role_id', $rootRole->id)
                    ->exists();

                if (!$hasRole) {
                    DB::table('role_user')->insert([
                        'user_id'    => $tciDev->id,
                        'role_id'    => $rootRole->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Sembrar permisos del sistema
            (new DatabaseSeeder())->run();

            return response()->json(['success' => true, 'message' => 'Sistema configurado correctamente.']);
        } catch (\Throwable $e) {
            Log::error('Setup configureSystem: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
