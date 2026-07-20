<?php

use App\Support\ProcessOnVisit;
use Illuminate\Support\Facades\Schedule;

/* VPS dengan cron CLI: php artisan schedule:run tiap menit. */
Schedule::call(fn () => ProcessOnVisit::runTick('cron'))->everyMinute()->name('javamaya-tick');
