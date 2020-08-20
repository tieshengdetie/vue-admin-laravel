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
        //同步禅道用户
        \App\Console\Commands\SyncZentaoData::class,
        //删除处理过的数据
        \App\Console\Commands\DeleteZentaoData::class,
    
        //同步邮件数据
        \App\Console\Commands\SyncEmaiToItservice::class,

        //异步邮件提醒
        \App\Console\Commands\SyncSendEmail::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $date = date("Y-m-d");

        $schedule->command('SyncZentaoData')->everyFifteenMinutes()->appendOutputTo(storage_path()."/logs/cron_log_{$date}.txt");


        $schedule->command('DeleteZentaoData')->daily()->appendOutputTo(storage_path()."/logs/cron_log_delete_{$date}.txt");

        $schedule->command('SyncEmaiToItservice')->everyFiveMinutes()->appendOutputTo(storage_path()."/logs/cron_log_email_{$date}.txt")->withoutOverlapping();

        $schedule->command('SyncSendEmail')->everyMinute()->appendOutputTo(storage_path()."/logs/cron_log_send_email_{$date}.txt")->withoutOverlapping();
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
