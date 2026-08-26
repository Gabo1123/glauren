<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Respaldo diario de la base de datos (a las 2:00 am), conserva los últimos 14
Schedule::command('db:backup --keep=14')->dailyAt('02:00');
