<?php

declare(strict_types=1);

namespace App\Console;

use Hyperf\Console\Scheduling\Schedule;
use Hyperf\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
     public function schedule(Schedule $schedule): void
     {
         // Schedule daily database backup at 2 AM
         $schedule->command('backup:database --compress --clean-old')->dailyAt('02:00');
         
         // Schedule weekly comprehensive backup on Sunday at 3 AM
         $schedule->command('backup:all --compress --clean-old')->weeklyOn(0, '03:00');
         
         // Schedule daily backup verification at 4 AM
         $schedule->command('backup:verify --type=all')->dailyAt('04:00');
         
         // Schedule daily backup monitoring to check for failed backups
         $schedule->command('backup:monitor --last-hours=24')->dailyAt('05:00');
     }

     public function commands(): void
     {
         $this->load(__DIR__ . '/Commands');
 
         require BASE_PATH . '/routes/console.php';
     }
}
