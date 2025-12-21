<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Process expired subscriptions daily at midnight
        $schedule->command('subscriptions:process-expired')
            ->daily()
            ->at('00:00')
            ->withoutOverlapping();

        // Send expiry reminders daily at 9 AM for subscriptions expiring in 7 days
        $schedule->command('subscriptions:send-expiry-reminders --days=7')
            ->daily()
            ->at('09:00')
            ->withoutOverlapping();

        // Send expiry reminders for subscriptions expiring in 3 days
        $schedule->command('subscriptions:send-expiry-reminders --days=3')
            ->daily()
            ->at('09:00')
            ->withoutOverlapping();

        // Send expiry reminders for subscriptions expiring tomorrow
        $schedule->command('subscriptions:send-expiry-reminders --days=1')
            ->daily()
            ->at('09:00')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
