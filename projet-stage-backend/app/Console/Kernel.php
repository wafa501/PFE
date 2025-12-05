<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

     protected function schedule(Schedule $schedule)
     {
        
        $schedule->command('app:facebook-save-pages-posts')->dailyAt('01:25');
        $schedule->command('app:facebook-save-my-posts-reactions')->dailyAt('01:25');
        $schedule->command('app:facebook-save-pages-posts-reactions')->dailyAt('01:25');
        $schedule->command('app:facebook-save-my-stats')->dailyAt('01:25'); 
        $schedule->command('app:update-other-pages-details')->dailyAt('01:25');
        $schedule->command('app:facebook-save-my-posts')->dailyAt('01:25');
         $schedule->command('facebook:fetch-page-data')->dailyAt('01:25');
         $schedule->command('fetch:statistics')->dailyAt('01:25');
         $schedule->command('fetch:update-organization')->dailyAt('01:25');
         $schedule->command('fetch:update-my-posts-data')->dailyAt('01:25');
         $schedule->command('fetch:update-other-posts-data')->dailyAt('01:25');
         $schedule->command('posts:check-thresholds')->dailyAt('03:25');
         $schedule->command('facebook:check-fan-count')->hourly();
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
