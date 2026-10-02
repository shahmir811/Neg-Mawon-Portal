<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Database-only backup to S3 (ngmcleaning-prod in production, see config/backup.php).
// Production only — on dev, run `php artisan backup:run --only-db` by hand when needed;
// nothing triggers Laravel's scheduler on a laptop that isn't reliably running.
Schedule::command('backup:run --only-db')
    ->everySixHours()
    ->environments(['production'])
    ->withoutOverlapping();

// Prune backups older than 30 days, first Sunday of every month (not spatie's usual
// daily cadence — see config/backup.php cleanup.default_strategy for the 30-day rule).
Schedule::command('backup:clean')
    ->cron('0 0 1-7 * 0')
    ->environments(['production'])
    ->withoutOverlapping();
