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
        // Daily database backup at 2 AM
        $schedule->command('backup:database --clean-old')->dailyAt('02:00');
        
        // Weekly file system backup on Sundays at 3 AM
        $schedule->command('backup:filesystem --clean-old')->weeklyOn(0, '03:00');
        
        // Weekly configuration backup on Saturdays at 1 AM
        $schedule->command('backup:config --clean-old')->weeklyOn(6, '01:00');
        
        // Daily backup verification at 4 AM
        $schedule->command('backup:verify --report')->dailyAt('04:00');
    }

    public function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
