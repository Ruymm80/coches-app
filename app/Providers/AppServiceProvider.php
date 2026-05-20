<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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

        View::composer('layouts.navigation', function ($view) {
            $user = auth()->user();
            $view->with('unreadMessages', $user ? $user->unreadMessagesCount() : 0);
        });
    }
}
