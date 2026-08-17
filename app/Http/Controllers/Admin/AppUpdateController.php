<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Cache, Log};
use Native\Laravel\Facades\AutoUpdater;

class AppUpdateController extends Controller
{
    //funcion consultada por polling desde el navegador para saber si hay una actualizacion
    //disponible/descargando/lista -- el estado real lo llenan los listeners en
    //EventServiceProvider a partir de los eventos que ya dispara Electron solo.
    public function status()
    {
        $status = Cache::get('nativephp_update_status', ['state' => 'none']);

        // si quedo guardado "lista para instalar" (o "disponible"/"descargando") de una
        // version que YA se alcanzo o se supero (no solo si es exactamente igual -- alguien
        // pudo saltarse una version, ej. estaba en 1.0.5 y el cache seguia diciendo 1.0.5
        // porque este mismo fix no existia todavia en ese build), es que ya se aplico y la
        // app reinicio, pero nadie limpio el cache -- el banner se queda ofreciendo reinstalar
        // algo que ya esta instalado (y Electron correctamente rechaza con "No valid update
        // available"). Se autolimpia aqui con version_compare en vez de igualdad estricta.
        $cachedVersion = $status['version'] ?? null;
        if (in_array($status['state'] ?? null, ['available', 'downloading', 'ready'], true)
            && $cachedVersion
            && version_compare($cachedVersion, config('nativephp.version'), '<=')) {
            Cache::forget('nativephp_update_status');
            $status = ['state' => 'none'];
        }

        return response()->json($status);
    }

    //funcion para forzar una revision manual de actualizaciones (ademas de la automatica que
    //ya hace Electron al abrir la app)
    public function check()
    {
        try {
            AutoUpdater::checkForUpdates();
            return redirect()->back()->with('info', 'Buscando actualizaciones...');
        } catch (\Throwable $th) {
            Log::warning('No se pudo revisar actualizaciones: '.$th->getMessage());
            return redirect()->back()->with('error', 'No se pudo revisar actualizaciones.');
        }
    }

    //funcion para reiniciar la app y aplicar la actualizacion ya descargada
    public function install()
    {
        try {
            AutoUpdater::quitAndInstall();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            Log::warning('No se pudo instalar la actualizacion: '.$th->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
