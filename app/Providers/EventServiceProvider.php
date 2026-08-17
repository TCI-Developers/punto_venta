<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\{Event, Cache, Log};
use Native\Laravel\Events\AutoUpdater\{UpdateAvailable, UpdateNotAvailable, DownloadProgress, UpdateDownloaded, Error as UpdaterError};

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    // clave de cache donde se guarda el ultimo estado conocido del auto-updater de NativePHP,
    // para que la vista (via polling a /update-status) pueda mostrar un aviso al usuario --
    // estos eventos ya se disparan solos (Electron revisa actualizaciones al abrir la app),
    // aqui solo se escuchan y se persiste el estado; nada dispara el chequeo desde aqui.
    const CACHE_KEY = 'nativephp_update_status';

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen(UpdateAvailable::class, function (UpdateAvailable $event) {
            Cache::put(self::CACHE_KEY, ['state' => 'available', 'version' => $event->version], now()->addDays(7));
        });

        Event::listen(DownloadProgress::class, function (DownloadProgress $event) {
            Cache::put(self::CACHE_KEY, ['state' => 'downloading', 'percent' => round($event->percent, 1)], now()->addDays(7));
        });

        Event::listen(UpdateDownloaded::class, function (UpdateDownloaded $event) {
            Cache::put(self::CACHE_KEY, ['state' => 'ready', 'version' => $event->version], now()->addDays(7));
        });

        Event::listen(UpdateNotAvailable::class, function () {
            Cache::forget(self::CACHE_KEY);
        });

        Event::listen(UpdaterError::class, function (UpdaterError $event) {
            Log::warning('Error en auto-updater de NativePHP: '.$event->message);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
