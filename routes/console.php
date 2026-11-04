<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Respaldo diario de la base de datos (a las 2:00 am), conserva los últimos 14
Schedule::command('db:backup --keep=14')->dailyAt('02:00');

// Ventas: lista de a quién llamar (7:00 am) y seguimiento automático a quien no respondió (10:00 am)
Schedule::command('ventas:recordatorio')->dailyAt('07:00')->timezone('America/Bogota');
Schedule::command('ventas:seguimiento')->dailyAt('10:00')->timezone('America/Bogota');
