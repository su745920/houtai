<?php

namespace App\Http\Controllers\Api;

use App\Models\InsuranceClaimApply;
use App\Models\InsuranceRule;
use App\Models\RechargeRecord;
use App\Models\Setting;
use App\Models\UsersInsurance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Logic\MicroTradeLogic;
use App\Models\Users;
use App\Models\CurrencyQuotation;
use App\Models\Currency;
use App\Models\MicroSecond;
use App\Models\UsersWallet;
use App\Models\LockMining;
use App\Models\MarketHour;
use App\Models\CurrencyMatch;
use App\Models\InsuranceType;
use App\Models\MicroNumbers;
use App\Models\LockMiningOrder;
use App\Models\AccountLog;
use App\Models\CreditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App;


class FinancialController extends Controller
{
    public function earlyRedemption(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $id=$request->input("id",0);
        $user_id = Users::getUserId();
        //1.更新订单状态
        $order=LockMiningOrder::find($id);
        $money=$order->money;
        $lock_id=$order->lock_id;
        $product= LockMining::find($lock_id);
        $adance_redeem_falsify=$product->adance_redeem_falsify;
        //计算违约金 20221120 需要乘以剩余天数((提前的天数*比例*总金额*0.01))
        $adanceDay = floor((time() - strtotime($order->created_at)) / (60 * 60 * 24));
        $subDay = $order->day - $adanceDay;
        // $kq=$subDay*$money*$adance_redeem_falsify*0.01;
        $kq=$money*$adance_redeem_falsify*0.01; // 改

        $interest=$order->interest;//已经发放的利息

        $refund=$money-$interest;
        
        $us = DB::table('currency')->where('name', 'USDT')->first();
        $wallet = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        
        //20221120 解冻锁仓挖矿冻结资产
        //change_wallet_balance($wallet, 4, -$order->money, AccountLog::RELEASE_LOCK_MINING, '释放锁仓挖矿增加可用资产',33,true);
                              
        //3.退款
        if ($refund>0){
            change_wallet_balance($wallet, 4,$refund, AccountLog::RELEASE_LOCK_MINING, '锁仓挖矿扣提前赎回退款','Lock up mining deduction, early redemption, refund');
            change_wallet_lock_balance($wallet, 4, -$refund, AccountLog::RELEASE_LOCK_MINING, '释放锁仓挖矿减少冻结资产','Release lock up mining to reduce frozen assets',true);
        }
         //$order->update(['status' => 7]);
        DB::update("update  lock_mining_order  set status=7,complete_at=".time()." where id=$id");

        //4.扣除赎回费 (提前的天数*比例*总金额*0.01) 20221120
        if($kq > 0){
            change_wallet_balance($wallet, 4,-$kq, AccountLog::RELEASE_LOCK_MINING, '锁合挖矿扣提前赎回手续费','Lock in mining deduction early redemption handling fee');
            //5.扣除信用分
            if(!empty($product->adance_redeem_credit)){
                $user = Users::getById($user_id);

                $before = $user->credit_score;
                // $after = bc_sub((string)$user->credit_score,(string)$product->adance_redeem_credit);
                $after = $before;
                $user->credit_score = $after;
                $user->save();

                //6.写日志 20221120
                $creditLog = New CreditLog();
                $creditLog->user_id = $user_id;
                $creditLog->lock_mining_order_id = $id;
                $creditLog->before = $before;
                $creditLog->after = $after;
                $creditLog->change = $product->adance_redeem_credit;
                $creditLog->memo = "锁合挖矿扣提前赎回扣除信用分";
                $creditLog->en_memo = 48;
                $creditLog->save();
            }
        }
        return $this->success(['type'=>'ok','msg'=>'success']);
    }

}