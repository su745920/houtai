<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{TransactionIn,TransactionOut,TransactionComplete,CurrencyMatch,AccountLog, Users, UsersWallet, Setting,CurrencyQuotation};

class AutoTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动币币交易限价买卖';

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
        // $this->info('开始执行自动币币交易限价买卖脚本-' . $now->toDateTimeString());
        // 限价买卖:
        // 就是做个计划任务监控价格
        // 买入的话是实时价格小于或等于限价就执行市价
        // 卖出是实时价格大于或等于限价就执行市价
        
        // 先获取币种价格
        $bb_list = CurrencyQuotation::getCurrencyQuotationList();
         if(count($bb_list) == 0) {
            $this->info('最新市价获取失败!');
            return;
        }
        
         $data = Users::where('email','!=','')->select(['id'])->get();
         foreach ($data as $k => $v) {
             // 查询用户买入币币限价交易
             $transaction_in_list = TransactionIn::where(['user_id' => $v['id'],'status' => 0])->get();
               foreach ($transaction_in_list as $tk => $tv) {
                 // 遍历币种
                 foreach ($bb_list as $bk => $bv) {
                     if($tv['currency'] === $bv['id']) {
                        //  买入的话是实时价格小于或等于限价就执行市价
                        if($bv['close'] <= $tv['price']) {
                            try {
                                DB::beginTransaction();
                                $user = Users::findOrFail($v['id']);
                                
                                $trade = TransactionIn::lockForupdate()->findOrFail($tv['id']);
                                $currency_match = CurrencyMatch::where('currency_id', $trade->currency)
                                    ->where('legal_id', $trade->legal)
                                    ->firstOrFail();
                                
                                $shoud_refund_number = bc_mul($bv['close'], $trade->number, 8);
                                $currency_id = $trade->legal;
                                $trade_id = $trade->currency;
                                $type_name = "自动币币限价买入交易";
                                $type_name_en = "Automatic Coin Limit Buy Trading";
                                $currency_name = $currency_match->legal_name;
                                
                                $user_wallet = UsersWallet::where('user_id', $user->id)
                                    ->where('currency', $currency_id)
                                    ->firstOrFail();
                                $trading_wallet = UsersWallet::where('user_id', $user->id)
                                    ->where('currency', $trade_id)
                                    ->firstOrFail();
                                if (bc_comp($user_wallet->lock_change_balance, $shoud_refund_number) < 0) {
                                    throw new \Exception("强制交易{$type_name}失败,冻结余额不足".$user->id);
                                }
                
                                //扣除usdt冻结金额
                                change_wallet_balance(
                                    $user_wallet,
                                    1,
                                    -$shoud_refund_number,
                                    AccountLog::DEDUCT_THE_FROZEN_AMOUNT,
                                    "币币交易:SYS取消{$type_name}{$currency_match->symbol},扣除{$currency_name},交易号:{$trade->id}",
                                    "Currency transaction: sys cancel {$type_name_en}{$currency_match->symbol},deduction {$currency_name},transaction number:{$trade->id}",
                                    true
                                );
                
                                //增加交易币种数量
                                change_wallet_balance(
                                    $trading_wallet,
                                    1,
                                    $trade->number,
                                    AccountLog::MANDATORY_TRANSACTION,
                                    "币币交易:SYS交易{$type_name}{$currency_match->symbol},交易{$currency_name},交易号:{$trade->id}",
                                    "Currency transaction: sys cancel {$type_name_en}{$currency_match->symbol},transaction {$currency_name},transaction number:{$trade->id}"
                                );
                
                                // 删除该挂单
                                throw_unless($trade->delete(), new \Exception('交易失败:清除交易失败'));
                                //添加完成记录
                                $complete = new TransactionComplete();
                                $complete->way = 1; //挂买
                                $complete->type = $trade->type;
                                $complete->user_id = $user->id; //买方
                                $complete->from_user_id = $user->id; //卖方
                                $complete->price = $bv['close'];
                                $complete->number = $trade->number;
                                $complete->currency = $trade->currency;
                                $complete->legal = $trade->legal;
                                $complete->in_fee = 0; //写入手续费
                                $complete->create_time = time();
                                $complete->save();
                                $this->info('限价买入成功!当前市价:'.$bv['close'].'限价:'.$tv['price']);
                           
                                DB::commit();
                             } catch (\Exception $e) {
                                 $this->info('限价买入执行失败'.json_encode($e));
                                 DB::rollBack();
                             }
                            
                        }
                     }
                 }
             }
                
             // 查询用户卖出币币限价交易
             $transaction_out_list = TransactionOut::where(['user_id' => $v['id'],'status' => 0])->get();
               foreach ($transaction_out_list as $tk => $tv) {
                 // 遍历币种
                 foreach ($bb_list as $bk => $bv) {
                     if($tv['currency'] === $bv['id']) {
                        //  卖出是实时价格大于或等于限价就执行市价
                        if($bv['close'] >= $tv['price']) {
                            try{
                                DB::beginTransaction();
                                $user = Users::findOrFail($v['id']);
                                
                                $trade = TransactionOut::lockForupdate()->findOrFail($tv['id']);
                                $currency_match = CurrencyMatch::where('currency_id', $trade->currency)
                                    ->where('legal_id', $trade->legal)
                                    ->firstOrFail();
                                
                                $shoud_refund_number = bc_mul($bv['close'], $trade->number, 8);
                                $currency_id = $trade->legal;
                                $trade_id = $trade->currency;
                                $type_name = "自动币币限价卖出交易";
                                $type_name_en = "Automatic Coin Limit Sell Trading";
                                $currency_name = $currency_match->legal_name;
                                
                                $user_wallet = UsersWallet::where('user_id', $user->id)
                                    ->where('currency', $currency_id)
                                    ->firstOrFail();
                                $trading_wallet = UsersWallet::where('user_id', $user->id)
                                    ->where('currency', $trade_id)
                                    ->firstOrFail();
                                if (bc_comp($user_wallet->lock_change_balance, $shoud_refund_number) < 0) {
                                    throw new \Exception("强制交易{$type_name}失败,冻结余额不足".$user->id);
                                }
                                
                                 //扣除交易币数量
                                change_wallet_balance(
                                    $user_wallet,
                                    1,
                                    -$trade->number,
                                    AccountLog::DEDUCT_THE_FROZEN_AMOUNT,
                                    "币币交易:SYS取消{$type_name}{$currency_match->symbol},扣除{$currency_name},交易号:{$trade->id}",
                                    "Currency transaction:SYS cancel {$type_name}{$currency_match->symbol},deduction {$currency_name},Transaction number:{$trade->id}",
                                    true
                                );
                                //增加USDT
                                change_wallet_balance(
                                    $trading_wallet,
                                    1,
                                    $shoud_refund_number,
                                    AccountLog::MANDATORY_TRANSACTION,
                                    "币币交易:SYS交易{$type_name}{$currency_match->symbol},卖出{$currency_name},交易号:{$trade->id}",
                                    "Currency transaction:SYS cancel {$type_name}{$currency_match->symbol},sell out {$currency_name},Transaction number:{$trade->id}"
                                );
                
                                // 删除该挂单
                                throw_unless($trade->delete(), new \Exception('交易失败:清除交易失败'));
                                //添加完成记录
                                $complete = new TransactionComplete();
                                $complete->way = 2; //挂卖
                                $complete->type = $trade->type;
                                $complete->user_id = $user->id; //买方
                                $complete->from_user_id = $user->id; //卖方
                                $complete->price = $bv['close'];
                                $complete->number = $trade->number;
                                $complete->currency = $trade->currency;
                                $complete->legal = $trade->legal;
                                $complete->in_fee = 0; //写入手续费
                                $complete->create_time = time();
                                $complete->save();
                                $this->info('限价卖出成功!当前市价:'.$bv['close'].'限价:'.$tv['price']);
                                DB::commit();
                             } catch (\Exception $e) {
                                 $this->info('限价卖出执行失败'.json_encode($e));
                                 DB::rollBack();
                            }
                        }
                     }
                 }
             }
                
         }
        //   $this->info('执行成功');
    }
}
