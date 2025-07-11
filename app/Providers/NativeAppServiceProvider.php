<?php

namespace App\Providers;

use Native\Laravel\Facades\{Window, Menu};
use Native\Laravel\Contracts\ProvidesPhpIni;
use Illuminate\Support\Facades\Artisan;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Menu::create(
            Menu::make(
                Menu::fullscreen()->label('Pantalla Completa'),
                Menu::reload()->label('Recargar'),
                Menu::separator(),
                Menu::quit()->label('Salir'),
            ),
            Menu::make(
                Menu::route('migration')->label('Migration'),
             )->label('Development'),
             Menu::view(),
             Menu::edit(),
        );

             
        Window::open()->maximized();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
