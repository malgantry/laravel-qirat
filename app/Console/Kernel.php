<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\EvaluateAiModel;
use App\Console\Commands\MakeAdmin;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     * @var array<int, class-string>
     */
    protected $commands = [
        EvaluateAiModel::class,
        MakeAdmin::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Define scheduled commands if needed
    }

    protected function commands(): void
    {
        // Load additional command routes if you use them
        // $this->load(__DIR__.'/Commands');
    }
}
