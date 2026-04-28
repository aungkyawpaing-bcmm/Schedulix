<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:daily-tasks', function (\App\Services\NotificationService $notifications) {
    $count = $notifications->createDailyTaskNotifications();
    $this->info("Generated {$count} daily task notifications.");
})->purpose('Generate daily task reminder notifications');

Artisan::command('notifications:overdue', function (\App\Services\NotificationService $notifications) {
    $count = $notifications->createOverdueNotifications();
    $this->info("Generated {$count} overdue notifications.");
})->purpose('Generate overdue task notifications');

Artisan::command('notifications:risk', function (\App\Services\NotificationService $notifications) {
    $count = $notifications->createRiskNotifications();
    $this->info("Generated {$count} risk notifications.");
})->purpose('Generate risk alert notifications');

Artisan::command('notifications:pm-summary', function (\App\Services\NotificationService $notifications) {
    $count = $notifications->createPmSummaryNotifications();
    $this->info("Generated {$count} PM summary notifications.");
})->purpose('Generate project manager summary notifications');

Schedule::command('notifications:daily-tasks')->dailyAt('14:42');
Schedule::command('notifications:overdue')->dailyAt('14:42');
Schedule::command('notifications:risk')->dailyAt('14:42');
Schedule::command('notifications:pm-summary')->dailyAt('14:42');
