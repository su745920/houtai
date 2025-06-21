<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{LeverTransaction, Users, Setting,CurrencyQuotation};

class AutoLeverTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_lever_transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动合约交易限价买卖';

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
        // $this->info('开始执行限价买卖交易脚本-' . $now->toDateTimeString());
        // 限价买卖
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
             $lever_in_list = LeverTransaction::where(['user_id' => $v['id'],'status' => 0,'type' => 1])->get();
             foreach ($lever_in_list as $lk => $lv) {
                 // 遍历币种
                 foreach ($bb_list as $bk => $bv) {
                     if($lv['currency'] === $bv['id']) {
                         //  买入的话是实时价格小于或等于限价就执行市价
                        if($bv['close'] <= $lv['price']) {
                             try {
                                DB::beginTransaction();
                                $trade = LeverTransaction::lockForupdate()->findOrFail($lv['id']);
                                $trade->status = 1;
                                $trade->save();
                                DB::commit();
                                $this->info('限价买入成功!当前市价:'.$bv['close'].'限价:'.$lv['price']);
                             } catch (\Exception $e) {
                                 $this->info('限价买入执行失败'.json_encode($e));
                                 DB::rollBack();
                             }
                        }
                     }
                 }
             }
              // 查询用户卖出币币限价交易
             $lever_out_list = LeverTransaction::where(['user_id' => $v['id'],'status' => 0,'type' => 2])->get();
             foreach ($lever_out_list as $lk => $lv) {
                 // 遍历币种
                 foreach ($bb_list as $bk => $bv) {
                     if($lv['currency'] === $bv['id']) {
                         //  买入的话是实时价格小于或等于限价就执行市价
                        if($bv['close'] >= $lv['price']) {
                             try {
                                DB::beginTransaction();
                                $trade = LeverTransaction::lockForupdate()->findOrFail($lv['id']);
                                $trade->status = 1;
                                $trade->save();
                                DB::commit();
                                $this->info('限价买入成功!当前市价:'.$bv['close'].'限价:'.$lv['price']);
                             } catch (\Exception $e) {
                                 $this->info('限价买入执行失败'.json_encode($e));
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
