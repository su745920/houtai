<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{UsersWallet};

class AutoYesterdayUsersWalletTotal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_yesterday_users_wallet_total';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动统计昨日用户钱包的资产';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute console command.
     *
     * @return mixed
     */
    public function handle()
    {
        date_default_timezone_set('America/New_York'); // 设置时区
        $now = Carbon::now();
        $this->info('开始执行自动统计昨日用户钱包的资产脚本-' . $now->toDateTimeString());
        UsersWallet::chunk(3000, function ($usersWallets) {
            foreach ($usersWallets as $usersWallet) {
                // 判断今天是否更新过
                $carbonDate = Carbon::parse($usersWallet->yesterday_updated_at); // 将字符串转换为Carbon实例
                // 判断是否为当天
                $isToday = $carbonDate->isToday();
                if(!$isToday) {
                    $usersWallet->yesterday_legal_balance = $usersWallet->legal_balance;
                    $usersWallet->yesterday_change_balance = $usersWallet->change_balance;
                    $usersWallet->yesterday_lever_balance = $usersWallet->lever_balance;
                    $usersWallet->yesterday_earn_balance = $usersWallet->earn_balance;
                    $usersWallet->yesterday_micro_balance = $usersWallet->micro_balance;
                    $usersWallet->yesterday_updated_at = date("Y-m-d H:i:s");
                    $usersWallet->save();
                    // $this->info('执行id:'.$usersWallet->id);
                }
            }
        });
        $this->info('执行成功');
    }
}
