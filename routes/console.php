<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @phpstan-ignore-next-line */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly reconciliation: reset any IN_USE equipment with no active loan order.
// Catches edge cases that slipped past the service layer (manual DB ops, bugs, etc.)
Schedule::command('app:reconcile-equipment-status', ['--no-interaction'])
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground();