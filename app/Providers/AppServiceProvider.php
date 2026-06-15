<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Configuración inicial de la aplicación. Aquí se registran servicios y
 * personalizaciones globales (paginación, URL, view composers).
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Usar el estilo de paginación de Tailwind en lugar del de Bootstrap.
        Paginator::useTailwind();

        // En producción detrás de un proxy HTTPS (Railway, Heroku, etc.),
        // forzamos a Laravel a generar URLs https para que los assets
        // de Vite no se bloqueen por mixed content en el navegador.
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Inyecta en la barra de navegación el contador de mensajes sin leer,
        // sin tener que pasarlo manualmente desde cada controlador.
        View::composer('layouts.navigation', function ($view) {
            $user = auth()->user();
            $view->with('unreadMessages', $user ? $user->unreadMessagesCount() : 0);
        });
    }
}
