<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\BlockChain\Coin\CoinManager;
use App\Models\{Users, UsersWallet,LockMining,LockMiningOrder,AccountLog,WalletLog};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Log;
 
class LockMiningPlan extends Command
{
    protected $signature = 'do_lockmining';
    protected $description = '执行锁仓挖矿';


    public function handle()
    {
        $this->doLockmining();
      
    }
    /*
        每分钟执行一次
    */
    
    public function doLockmining(){
        $list = DB::table('lock_mining_order')->where('status',0)->orderBy('id','asc')->get();
        foreach ($list as $val) {
            // 查询当前实例
            $lockOrder = LockMiningOrder::where('id',$val->id)->lockForUpdate()->first();

            // 判断今天是否更新过
            $carbonDate = Carbon::parse($val->updated_at); // 将字符串转换为Carbon实例
            // 判断是否为当天
            $isToday = $carbonDate->isToday();
            // $this->setLog("是否为当天:".$isToday);
            if(!$isToday) {
                DB::beginTransaction();
                try{
                    $day = intval($val->day) * 86400;
                    $day=intval($day);
                    $created_at=intval($val->created_at);
                    
                    if($created_at+$day <= time()){
                      //进行处理
                      $lockOrder->updated_at = time();
                      $lockOrder->status = 1;
                      $lockOrder->save();
                        
                        $wallet = UsersWallet::where('user_id',$val->user_id)->where('currency', $val->from_name)->first();
                      
                        $number = $val->money;

                        $this->change_wallet_balance($wallet, 4, $number, AccountLog::RELEASE_LOCK_MINING, '释放锁仓挖矿增加可用资产','Release lock up mining to increase available assets');
                        $this->change_wallet_balance($wallet, 4, -$number, AccountLog::RELEASE_LOCK_MINING, '释放锁仓挖矿扣除冻结资产','Release lock up mining and deduct frozen assets',true);
                        
                        
                        //还有进行收益
                        $wallet = UsersWallet::where('user_id',$val->user_id)->where('currency', $val->to_name)->first();
                        $number = $val->money * $val->rate * $val->day * 0.01;
 
                        $this->change_wallet_balance($wallet, 4, $number, AccountLog::LOCK_MINING_PROFIT, '锁仓挖矿收益', 'Lock up mining profits');
                        $lockOrder->updated_at = time();
                        $lockOrder->complete_at = time();
                        $lockOrder->save();
                       DB::commit();
                       $this->setLog('执行成功id: '.$val->id);
                    }
                }catch(\Exception $e){
                     DB::rollback();
                    $this->setLog('执行错误id: '.$val->id.'错误:'.$e->getMessage());
                }
            }
        }
        
        // db::table('lock_mining_order')->where('status',0)->orderBy('id','asc')->chunk(100, function ($lockmining) {
        //     $lockmining = json_decode(json_encode($lockmining), true);
        //     foreach ($lockmining as $val) {
        //         // 判断今天是否更新过
        //         $carbonDate = Carbon::parse($val['updated_at']); // 将字符串转换为Carbon实例
        //         // 判断是否为当天
        //         $isToday = $carbonDate->isToday();
        //         // $this->setLog("是否为当天:".$isToday);
        //         if(!$isToday) {
        //             DB::beginTransaction();
        //             try{
        //                 $day = intval($val['day']) * 86400;
        //                 $day=intval($day);
        //                 $created_at=intval($val['created_at']);
                        
        //                 if($created_at+$day <= time()){
        //                 //   $this->setLog(date("Y-m-d H:i:s")."开始结算锁仓挖矿===>开始时间".$val['created_at']."总天数===".$val['day']);
        //                   //进行处理
        //                     LockMiningOrder::where('id',$val['id'])->update(['updated_at' => time()]);
                            
        //                     $wallet = UsersWallet::where('user_id',$val['user_id'])->where('currency', $val['from_name'])->first();
                          
                          
        //                     $number = $val['money'];

        //                     $this->change_wallet_balance($wallet, 4, $number, AccountLog::RELEASE_LOCK_MINING, '释放锁仓挖矿增加可用资产','Release lock up mining to increase available assets');
        //                     $this->change_wallet_balance($wallet, 4, -$number, AccountLog::RELEASE_LOCK_MINING, '释放锁仓挖矿扣除冻结资产','Release lock up mining and deduct frozen assets',true);
                            
                            
        //                     //还有进行收益
        //                     $wallet = UsersWallet::where('user_id',$val['user_id'])->where('currency', $val['to_name'])->first();
        //                     $number = $val['money'] * $val['rate'] * $val['day'] * 0.01;
     
        //                     $this->change_wallet_balance($wallet, 4, $number, AccountLog::LOCK_MINING_PROFIT, '锁仓挖矿收益', 'Lock up mining profits');
        //                   LockMiningOrder::where('id',$val['id'])->update(['status' => 1,'complete_at' => time(),'updated_at' => time()]);
        //                   DB::commit();
        //                   $this->setLog('执行成功id:'.$val['id']);
        //                 }
        //             }catch(\Exception $e){
        //                  DB::rollback();
        //                 $this->setLog('执行错误id:'.$val['id'].'错误:'.$e->getMessage());
        //             }
        //         }
        //     }
        // });
    }
    
    function setLog($logMessage) {
         // 定义日志文件的路径
        $logDir = base_path() . '/storage/logs/';
        $logFile = $logDir . 'do_lockmining.log';
         
        // 检查目录是否存在，如果不存在则创建
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true); // 第三个参数为true表示递归创建父目录
        }
         
        // 要写入的内容
        $logMessage = date('Y-m-d H:i:s'). $logMessage . "\n";
         
        // 写入日志文件
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    function change_wallet_balance(&$wallet, $balance_type, $change, $account_log_type, $memo = '',$enmemo = '', $is_lock = false, $from_user_id = 0, $extra_sign = 0, $extra_data = '', $zero_continue = false, $overflow = false){
            $param = compact('balance_type', 'change', 'account_log_type', 'memo', 'enmemo', 'is_lock', 'from_user_id', 'extra_sign', 'extra_data', 'zero_continue', 'overflow');
                extract($param);
                $fields = [
                     'legal_balance',//资金账户
                    'change_balance',//币币账户
                    'lever_balance',//合约账户
                    'micro_balance',//秒合约
                    'earn_balance'//理财
                ];
                $field = ($is_lock ? 'lock_' : '') . $fields[$balance_type];
                $wallet = $wallet->lockForUpdate()->findOrFail($wallet->getKey()); //钱包获取最新钱包数据并锁定
                $user_id = $wallet->user_id;
                $before = $wallet->$field;
                $after = bc_add($before, $change);
                if(bc_comp($after, '0') >= 0 ){
                    $now = time();
                    AccountLog::unguard();
                    $order_no = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                    $account_log = AccountLog::create([
                        'user_id' => $user_id,
                        'value' => $change,
                        'info' => $memo,
                        'en_info' => $enmemo,
                        'type' => $account_log_type,
                        'created_time' => $now,
                        'currency' => $wallet->currency,
                        'is_lock' =>$is_lock?1:0,
                        'order_no'=>$order_no
                    ]);
                    WalletLog::unguard();
                    $wallet_log = WalletLog::create([
                        'account_log_id' => $account_log->id,
                        'user_id' => $user_id,
                        'from_user_id' => $from_user_id,
                        'wallet_id' => $wallet->id,
                        'balance_type' => $balance_type,
                        'lock_type' => $is_lock ? 1 : 0,
                        'before' => $before,
                        'change' => $change,
                        'after' => $after,
                        'memo' => $memo,
                        'en_memo' => $enmemo ,
                        'extra_sign' => $extra_sign,
                        'extra_data' => $extra_data,
                        'create_time' => $now,
                        'order_no'=>$order_no
                    ]);
                    $wallet->$field = $after;
                    $result = $wallet->save();
                    if (!$result) {
                        throw new \Exception('钱包写入失败');
                    }
                }else {
                    throw new \Exception($field.'余额不足');
                }
        }
}
