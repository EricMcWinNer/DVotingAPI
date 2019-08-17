<?php

namespace App\Console;

use App\Election;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

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
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->call(function () {
            $currentElection = Election::where('status', 'pending')
                ->orWhere('status', 'ongoing')
                ->orWhere('status', 'completed')
                ->orderBy('id', 'desc')
                ->first();
            if (Carbon::now()->greaterThan(Carbon::parse($currentElection->start_date)) && Carbon::now()->lessThan(Carbon::parse($currentElection->end_date)) && $currentElection->status === 'pending') {
                $currentElection->status = 'ongoing';
                $currentElection->save();
            } else if (Carbon::now()->greaterThanOrEqualTo(Carbon::parse($currentElection->end_date))) {
                $currentElection->status = 'completed';
                $currentElection->save();
            } else {
                //DO NOTHING FOR NOW
            }
        })->everyMinute()->appendOutputTo("C:\Users\Eric McWinNEr\Desktop\laravelcronoutput.txt");
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
