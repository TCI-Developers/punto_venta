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
        return response()->json(Cache::get('nativephp_update_status', ['state' => 'none']));
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
