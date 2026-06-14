<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // En producción detrás de un proxy HTTPS (Railway, Heroku, etc),
        // forzamos a Laravel a generar URLs https para que los assets
        // de Vite no se bloqueen por mixed content.
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.navigation', function ($view) {
            $user = auth()->user();
            $view->with('unreadMessages', $user ? $user->unreadMessagesCount() : 0);
        });
    }
}
