<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Utils\RPC;
use App\Models\{AppVersion, Bank, Setting ,AccountLog, Token, Users,UsersWallet,MarketHour,ChargeHash};

use App\Jobs\{LeverUpdate, SendMarket};
use App;

class NoticesController extends Controller
{
    public function syncnotice(Request $request)
    {
        $currency_type = $request->input('currency_type', '');
        
        $amount = $request->input('amount', 0);
        $from_address = $request->input('from_address', '');
        $to_address = $request->input('to_address', '');
        $trans_time = $request->input('trans_time', '');
        $txn_hash = $request->input('txn_hash', '');
        
        $currency = "";
        $code = "";
        if($currency_type =="trc20-usdt"){
            $currency = "23";
            $code = "USDT";
        }else if($currency_type =="erc20-usdt"){
            $currency = "23";
            $code = "USDT";
        }
        
        
        try {
            DB::beginTransaction();
            $user_wallet = UsersWallet::where("address",$to_address)->where("currency",$currency)->first();
            if(!empty($user_wallet))
            {
                $info = ChargeHash::where('txid',$txn_hash)->first();
                if(empty($info)){
                    // $diff_balance = bc_add($user_wallet->change_balance,$amount,6);
                    // $user_wallet->change_balance = $diff_balance;
                    // $res = $user_wallet->save();
                    
                    $hash = new ChargeHash();
                    $hash->code =$code;
                    $hash->type =$currency_type;
                    $hash->currency_id =$currency;
                    $hash->token_address ="";
                    $hash->txid =$txn_hash;
                    $hash->amount =$amount;
                    $hash->index ="";
                    $hash->sender =$from_address;
                    $hash->recipient =$to_address;
                    $hash->status = 1;
                    $hash->created_at = date('Y-m-d H:i:s',time());
                    $hash->time =$trans_time;  
                    $hash->blocknum ="";
                    $res = $hash->save();
                    
                    if($res){
                        $balance_to = Setting::getValueByKey('recharge_to_balance', 1); // 充币到哪个账户(1.法币,2.币币,3.杠杆)
                        
                        $result = change_wallet_balance(
                            $user_wallet,
                            $balance_to,
                            $amount,
                            AccountLog::CHAIN_RECHARGE,
                            "{$user_wallet->currencyCoin->type}链上充币增加",
                            "{$user_wallet->currencyCoin->type} Charge on the chain increased"
                        );
                    }
                }
                
                DB::commit();
                echo "success";
            }
            else
            {
                DB::commit();
                echo "error";
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            echo "error".$ex->getMessage();
        }
        die;
    }
}
