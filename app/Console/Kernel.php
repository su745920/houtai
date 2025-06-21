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
        Commands\Socket::class,
        Commands\AutoCancelLegal::class,
        Commands\UpdateBalance::class,
        Commands\ClearMarketVolume::class,
        Commands\UpdateHashStatus::class,
        Commands\ClearExpiredToken::class,
        Commands\UpdateCharge::class,
        Commands\LockMiningPlan::class,
        Commands\UnlockCurrencyProjectOrder::class,
        Commands\AutoLever::class,
        Commands\AutoTransaction::class,
        Commands\AutoLeverTransaction::class,
        Commands\AutoLastClose::class,
        Commands\AutoYesterdayUsersWalletTotal::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // //$schedule->command('update_hash_status')->everyFiveMinutes()->withoutOverlapping(); //更新哈希值状态
        // //$schedule->command('market:clear:volume')->withoutOverlapping()->dailyAt('00:00'); //清空24小时成交量
        // $schedule->command('lever:overnight')->dailyAt('00:01'); //收取隔夜费
        // $schedule->command('clear:tokens')->everyMinute()->withoutOverlapping(); // 清除过期token
        // $schedule->command('auto_cancel_legal')->everyMinute()->withoutOverlapping()->runInBackground()->appendOutputTo('./storage/logs/auto_cancel_legal.log');
        // $schedule->command('auto_confirm_legal')->everyMinute()->withoutOverlapping()->runInBackground()->appendOutputTo('./storage/logs/auto_confirm_legal.log');
        // //$schedule->command('update_charge')->everyMinute()->appendOutputTo('./update_charge.log')->withoutOverlapping(); //根据充币hash更新链上余额
        
        // $schedule->command('do_lockmining')->hourly()->withoutOverlapping()->runInBackground()->appendOutputTo('./storage/logs/do_lockmining.log');
        $schedule->command('do_unlock_currency_project_order')->hourly();
        
        $schedule->command('auto_lever')->everyMinute()->withoutOverlapping()->runInBackground()->appendOutputTo('./storage/logs/auto_lever.log');
        $schedule->command('auto_transaction')->everyMinute()->withoutOverlapping()->runInBackground()->appendOutputTo('./storage/logs/auto_transaction.log');
        $schedule->command('auto_lever_transaction')->everyMinute()->withoutOverlapping()->runInBackground()->appendOutputTo('./storage/logs/auto_lever_transaction.log');
        // $schedule->command('auto_last_close')->everyMinute()->withoutOverlapping()->runInBackground()->appendOutputTo('./storage/logs/auto_last_close.log');
        $schedule->command('auto_yesterday_users_wallet_total')->dailyAt('00:00')->withoutOverlapping()->runInBackground()->appendOutputTo('./storage/logs/auto_yesterday_users_wallet_total.log');
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
