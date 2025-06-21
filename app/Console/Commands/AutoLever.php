<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{LeverTransaction, Users, UsersWallet, Setting,CurrencyQuotation,AccountLog};

class AutoLever extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_lever';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动爆仓交易';

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
        $now = Carbon::now();
        // $this->info('开始执行自动爆仓交易脚本-' . $now->toDateTimeString());
        // 自动爆仓逻辑
        // 逐仓的话就是监听单个订单 当浮动盈亏大于保证金就自动点平仓 
        // 全仓就监听所有订单 浮动盈亏负数>(保证金+合约账户余额）
        // 先获取币种价格
        $bb_list = CurrencyQuotation::getCurrencyQuotationList();
         if(count($bb_list) == 0) {
            $this->info('最新市价获取失败!');
            return;
        }
         $data = Users::select(['id'])->get();
         foreach ($data as $k => $v) {
             // 查询用户的逐仓秒合约交易
             $lever_one_list = LeverTransaction::where(['user_id' => $v['id'],'status' => 1,'warehouse_type' => 0])->get();
             foreach ($lever_one_list as $lk => $lv) {
                 // 遍历币种
                 foreach ($bb_list as $bk => $bv) {
                     if($lv['currency'] == $bv['currency_id']) {
                        // 盈亏 =（当前价-开仓价）或者（开仓价-当前价）×手数×杠杆
                         $deff_price = $lv['type'] == 1 ? bc_sub($bv['close'],$lv['price']) :  bc_sub($lv['price'],$bv['close']);
                         $win_loss = $deff_price * $lv['share'] * $lv['multiple'];
                        // 保证金
                        $caution_money = $lv['caution_money'];
                       // 判断是否大于保证金
                       // 判断是否大于保证金
                        //  if($v['id'] == 858) {
                        //       $this->info('满足条件逐仓爆仓数量id:'.$lv['id'].'当前价:'.$bv['close'].'盈亏:'.$win_loss.'保证金:'.$caution_money);
                        //  }
                        if($win_loss < 0 && abs($win_loss) > $caution_money) {
                             $this->info('满足条件逐仓爆仓数量id:'.$lv['id'].'当前价:'.$bv['close'].'盈亏:'.$win_loss.'保证金:'.$caution_money);
                            // 处理逐仓爆仓
                            try {
                                $result = LeverTransaction::leverClose($lv, 2,$bv['close']);
                                if($result) {
                                    $this->info('逐仓爆仓执行成功'. $now->toDateTimeString());
                                }
                            } catch (\Exception $e) {
                                $this->info('逐仓爆仓失败'.json_encode($e));
                            }
                        }
                     }
                 }
                
             }
             
             // 查询用户的全仓秒合约交易
             $lever_all_list = LeverTransaction::where(['user_id' => $v['id'],'status' => 1,'warehouse_type' => 1])->get();
             // 查询用户合约账户余额
             $user_wallet_all = $this->contractsWalletList($v['id']);
            //  查询用户所有全仓订单保证金总和
            $user_all_caution_money = LeverTransaction::where(['user_id' => $v['id'],'status' => 1,'warehouse_type' => 1])->sum('caution_money');
            // 用户所有全仓盈亏总和
             $user_all_win_loss = 0; // 单个用户所有全仓盈亏总数
             foreach ($lever_all_list as $lk => $lv) {
                 // 遍历币种
                 foreach ($bb_list as $bk => $bv) {
                     if($lv['currency'] == $bv['currency_id']) {
                        // 盈亏 =（当前价-开仓价）或者（开仓价-当前价）×手数×杠杆
                        $deff_price = $lv['type'] == 1 ? bc_sub($bv['close'],$lv['price']) :  bc_sub($lv['price'],$bv['close']);
                        $win_loss = $deff_price * $lv['share'] * $lv['multiple'];
                        $user_all_win_loss += $win_loss;
                     }
                 }
                
             }
             $total = $user_all_caution_money + $user_wallet_all;
            //  if($v['id'] == 1203456) {
            //       $this->info('全仓盈亏:'.$user_all_win_loss.'保证金:'.$user_all_caution_money.'合约账号余额:'.$user_wallet_all);
            //  }
             // 判断 所有全仓浮动盈亏负数 > 所有全仓(保证金）+ 合约账户余额
              if(count($lever_all_list) > 0 && $user_all_win_loss < 0 && abs($user_all_win_loss) > $total) {
                 try {
                     DB::beginTransaction();
                      $result_num = 0; // 处理成功平仓结果数
                      foreach ($lever_all_list as $lk => $lv) {
                         // 遍历币种
                         foreach ($bb_list as $bk => $bv) {
                             if($lv['currency'] == $bv['currency_id']) {
                                $result = LeverTransaction::leverClose($lv, 2,$bv['close']);
                                if($result) {
                                    $result_num ++;
                                    $this->info('全仓爆仓id:'.$lv['id']);
                                }
                             }
                         }
                     }
                     if($result_num > 0 && $result_num == count($lever_all_list)) {
                         // 清空用户合约金额
                        //  $reset_wallet = UsersWallet::where('currency',23)->where("user_id", $v['id'])->update(['lever_balance' => 0]);
                        $legal_wallet = UsersWallet::where("user_id", $v['id'])
                        ->where("currency", 23)
                        ->lockForUpdate()
                        ->first();
                         $reset_wallet = change_wallet_balance(
                            $legal_wallet,
                            2, // 3, maxCore更改
                            -$user_wallet_all,
                            AccountLog::LEVER_TRANSACTION_FROZEN,
                            '全仓暴仓处理：余额(退回保证金:' . $user_all_caution_money . ',结算总盈亏:' . $user_all_win_loss . ')',
                            'Full warehouse liquidation handling: balance (refund of margin ' . $user_all_caution_money . ',Settlement of total profit and loss:' . $user_all_win_loss . ')',
                            false,
                            0,
                            abs($user_all_win_loss) <= $total ? 0 : 1, //1代表有差额
                            '',
                            true, //余额为0仍然要平仓
                            false, //余额不足时扣成负数
                            true // 是否清空用户余额
                        );
                         if($reset_wallet) {
                             DB::commit();
                            $this->info('全仓爆仓执行成功'. $now->toDateTimeString());
                         }else {
                             $this->info('用户合约钱包USDT清空失败!'. $now->toDateTimeString());
                         }
                     }
                 } catch (\Exception $e) {
                     $this->info('全仓爆仓失败'.json_encode($e->getMessage()));
                     DB::rollBack();
                 }
             }
             
         }
        //   $this->info('执行成功');
    }
    // 获取用户合约的usdt余额
     public function contractsWalletList($user_id)
    {
        if (empty($user_id)) {
            return 0;
        }
        $USDTRate = Setting::getValueByKey('USDTRate', 7.22);
        $user_wallet = UsersWallet::with(['currencyCoin'])->where('user_id', $user_id)
            ->whereHas('currencyCoin')->get();
        $user_wallet->transform(function ($item, $key) {
            $item->setVisible([
                'id', 'currency', 'sort',
                'lever_balance',
            ]);
            return $item;
        });
        $total = 0;
        //账户资产只显示 USDT
        $lever_list = $user_wallet->where('is_lever', 1)->whereIn('currency', [23])->sortByDesc("lever_balance")->values()->all();
        foreach ($lever_list as $k => $v) {
            $total += $v['lever_balance'];
        }
        return $total;
    }
}
