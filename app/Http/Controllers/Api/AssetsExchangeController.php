<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{AccountLog, Users, Setting, UsersWallet};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

use App\Models\SellerAccountLog;
use App\Models\WalletLog;
use App;
class AssetsExchangeController extends Controller
{
    public function initUserData(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();

        $fromCoin=$request->input('fromCoin');

        $us = DB::table('currency')->where('name', $fromCoin)->first();
        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        $accountBalance= isset($wal->lever_balance) ? $wal->lever_balance : '0.00';
        $bibiBalance= isset($wal->change_balance) ? $wal->change_balance : '0.00';

        $jo["accountBalance"]=$accountBalance;
        $jo["bibiBalance"]=$bibiBalance;

        return $this->success($jo);



    }
    public function doExchange(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $fromAssets=$request->input('fromAssets');

        $fromCoin=$request->input('fromCoin');
        $fromAmount=$request->input('fromAmount');
        $us = DB::table('currency')->where('name', $fromCoin)->first();
        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        $accountBalance= isset($wal->lever_balance) ? $wal->lever_balance : '0.00';
        $bibiBalance= isset($wal->change_balance) ? $wal->change_balance : '0.00';

        if ($fromAssets=="account"){// 账户资产-》币币资产
            if ($fromAmount>$accountBalance){
                $j["msg"]=trans('wallet.zhzcyebz');
                return $this->success($j);
            }else{
                $this->changeWalletBalance($wal, 2, -$fromAmount, AccountLog::LOCK_MINING, $fromCoin.'资产转换扣除账户资产',36,"false");
                $this->changeWalletBalance($wal, 1, $fromAmount, AccountLog::LOCK_MINING, $fromCoin.'资产转换增加币币资产',35,"false");
                $j["msg"]=trans('wallet.zhcg');
                return $this->success($j);
            }
        }
        if ($fromAssets=="bibi"){
            if ($fromAmount>$bibiBalance){
                $j["msg"]=trans('wallet.bbzcyebz');
                return $this->success($j);
            }else{
                $this->changeWalletBalance($wal, 1, -$fromAmount, AccountLog::LOCK_MINING, $fromCoin.'资产转换扣除币币资产',36,"false");
                $this->changeWalletBalance($wal, 2, $fromAmount, AccountLog::LOCK_MINING, $fromCoin.'资产转换增加账户资产',35,"false");
                $j["msg"]=trans('wallet.zhcg');
                return $this->success($j);
            }
        }


    }


    function changeWalletBalance(&$wallet, $balance_type, $change, $account_log_type, $memo = '',$enmemo = '', $is_lock = false, $from_user_id = 0, $extra_sign = 0, $extra_data = '', $zero_continue = false, $overflow = false)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        //为0直接返回真不往下再处理
        if (!$zero_continue && bc_comp($change, '0') == 0) {
            $path = base_path() . '/storage/logs/wallet/';
            $filename = date('Ymd') . '.log';
            file_exists($path) || @mkdir($path);
            error_log(date('Y-m-d H:i:s') . ' 改变金额为0,不处理' . PHP_EOL, 3, $path . $filename);
            return true;
        }

        $param = compact('balance_type', 'change', 'account_log_type', 'memo', 'enmemo', 'is_lock', 'from_user_id', 'extra_sign', 'extra_data', 'zero_continue', 'overflow');
        try {
            if (!in_array($balance_type, [1, 2, 3,4,5])) {
                throw new \Exception(trans('wallet.hblxbzq'));
            }
            DB::transaction(function () use (&$wallet, $param) {
                extract($param);
                $fields = [
                    '',
                    //'legal_balance',
                    'change_balance',
                    'lever_balance',
                    //'micro_balance',
                    //'insurance_balance'
                ];

                $field =$fields[$balance_type];
                $wallet = $wallet->lockForUpdate()->findOrFail($wallet->getKey()); //钱包获取最新钱包数据并锁定
                $user_id = $wallet->user_id;
                $before = $wallet->$field;

                $after = bc_add($before, $change);

                //判断余额是否充足
                if (bc_comp($after, '0') < 0 && !$overflow) {
                    //throw new \Exception('Wallet ' . $field . ' not sufficient funds');
                    throw new \Exception(trans('microorder.wallet') . ' ' .$field.trans('microorder.yezjbz'));

                }
                $now = time();
                AccountLog::unguard();
                $order_no=$osn = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                $account_log = AccountLog::create([
                    'user_id' => $user_id,
                    'value' => $change,
                    'info' => $memo,
                    'en_info' => $enmemo,
                    'type' => $account_log_type,
                    'created_time' => $now,
                    'currency' => $wallet->currency,
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
                    'extra_sign' => $extra_sign,
                    'extra_data' => $extra_data,
                    'create_time' => $now,
                    'order_no'=>$order_no
                ]);
                $wallet->$field = $after;
                $result = $wallet->save();
                if (!$result) {
                    throw new \Exception(trans('wallet.zhsb'));
                }
            });
            return true;
        } catch (\Exception $e) {
            throw $e;
            return false;
            // return $e->getMessage();
        } finally {
            AccountLog::reguard();
            WalletLog::reguard();
        }
    }

}
