<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backups off-site (spatie/laravel-backup): limpia las copias viejas y crea la del día.
// Requiere el scheduler activo en producción (Laravel Cloud → Scheduler).
Schedule::command('backup:clean')->daily()->at('01:30')->withoutOverlapping();
Schedule::command('backup:run')->daily()->at('02:00')->withoutOverlapping();
