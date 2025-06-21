<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\BlockChain\Coin\CoinManager;
use App\Models\{Users, UsersWallet,CurrencyProjectOrder,AccountLog,WalletLog};
use Illuminate\Support\Facades\DB;
class UnlockCurrencyProjectOrder extends Command
{
    protected $signature = 'do_unlock_currency_project_order';
    protected $description = '执行解冻新币';


    public function handle()
    {
        $this->doUnlockCurrencyProjectOrder();
      
    }
    /*
        每小时执行一次
    */
    
    public function doUnlockCurrencyProjectOrder(){
       
        db::table('currency_project_order')->where('status',3)->orderBy('id','asc')->chunk(100, function ($order) {
            $order = json_decode(json_encode($order), true);
            foreach ($order as $val) {
                try{
                   if($val['end_at'] >= time()){
                        
                    //     //进行处理
                    
                        $wallet = UsersWallet::where('user_id',$val['user_id'])->where('currency', $val['currency_id'])->first();                                     
                        $number = $val['passed_amount'];
                        $this->change_wallet_balance($wallet, 2, $number, AccountLog::RELEASE_LOCK_MINING, '新币申购解冻资产','New currency subscription unfreezes assets');
                        CurrencyProjectOrder::where('id',$val['id'])->update(['status' => 4]);
                     }
                }catch(\Excption $e){
                    $this->error($e->getMessage());
                    
                }
            }
        });
    }
    


    function change_wallet_balance(&$wallet, $balance_type, $change, $account_log_type, $memo = '',$enmemo = '', $is_lock = false, $from_user_id = 0, $extra_sign = 0, $extra_data = '', $zero_continue = false, $overflow = false)
    {
   
  
    
    $param = compact('balance_type', 'change', 'account_log_type', 'memo', 'enmemo', 'is_lock', 'from_user_id', 'extra_sign', 'extra_data', 'zero_continue', 'overflow');
       
       
                extract($param);
                $fields = [
                    '',
                    'legal_balance',
                    'change_balance',
                    'lever_balance',
                    'micro_balance',
                    'insurance_balance'
                ];
                $field = ($is_lock ? 'lock_' : '') . $fields[$balance_type];
                $wallet = $wallet->lockForUpdate()->findOrFail($wallet->getKey()); //钱包获取最新钱包数据并锁定
                $user_id = $wallet->user_id;
                $before = $wallet->$field;
                $after = bc_add($before, $change);
                if(bc_comp($after, '0') >= 0 ){
                    $now = time();
                    AccountLog::unguard();
                    $account_log = AccountLog::create([
                        'user_id' => $user_id,
                        'value' => $change,
                        'info' => $memo,
                        'en_info' => $enmemo,
                        'type' => $account_log_type,
                        'created_time' => $now,
                        'currency' => $wallet->currency,
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
                        'extra_sign' => $extra_sign,
                        'extra_data' => $extra_data,
                        'create_time' => $now,
                    ]);
                    $wallet->$field = $after;
                    $result = $wallet->save();
                
                }

        }
            

}
