<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compatibilidad con MySQL/MariaDB antiguos (evita "key too long" con utf8mb4)
        Schema::defaultStringLength(191);

        // Fechas en español en todo el sitio ("hace 2 horas", "3 de noviembre").
        \Carbon\Carbon::setLocale('es');
    }
}
