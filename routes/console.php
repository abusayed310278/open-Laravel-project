<?php

use App\Jobs\CleanupExpiredCarts;
use App\Jobs\ExpireListings;
use App\Jobs\ProcessPendingPayouts;
use App\Jobs\SendSubscriptionExpiryReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ExpireListings)->daily();
Schedule::job(new SendSubscriptionExpiryReminders)->daily();
Schedule::job(new ProcessPendingPayouts)->daily();
Schedule::job(new CleanupExpiredCarts)->weekly();
