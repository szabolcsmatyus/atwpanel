<?php

namespace Pterodactyl\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        // Execute scheduled commands for servers every minute, as if there was a normal cron running.
        $schedule->command('p:schedule:process')->everyMinute()->withoutOverlapping();

        // Every 30 minutes, run the backup pruning command so that any abandoned backups can be removed
        // from the UI view for the server.
        $schedule->command('p:maintenance:prune-backups', [
            '--since-minutes' => '30',
        ])->everyThirtyMinutes();

        // Every day cleanup any internal backups of service files.
        $schedule->command('p:maintenance:clean-service-backups')->daily();
    }
}
