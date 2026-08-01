<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keeps the GIS map's Brunei weather cache pre-warmed once a day, independent of
// the live 20-min cache / 5-min client poll already used when someone has the map open.
Schedule::command('weather:refresh-brunei')
    ->dailyAt('06:00')
    ->timezone('Asia/Brunei')
    ->name('refresh-brunei-weather')
    ->withoutOverlapping();

// Refreshes every cell in the hex drainage risk grid from live rainfall forecasts.
Schedule::command('risk:sync')
    ->hourly()
    ->withoutOverlapping();
