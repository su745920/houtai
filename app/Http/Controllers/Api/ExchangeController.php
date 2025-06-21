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

class ExchangeController extends Controller
{
    //兑换 比如 BTC-》ETH 目前只能在帐户资产里面兑换  币币资产没有这个功能  2023-05-09
    public function ex_v2(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $url="https://api.huobi.pro/market/detail/merged?symbol=btcusdt";
        $result= $this->curl($url);
        $result=json_decode($result);
        $tick=$result->tick;
        $btcClose=$tick->close;
        $url="https://api.huobi.pro/market/detail/merged?symbol=ethusdt";
        $result= $this->curl($url);
        $result=json_decode($result);
        $tick=$result->tick;
        $ethClose=$tick->close;
        $jo["btcPrice"]=$btcClose;
        $jo["ethPrice"]=$ethClose;
        $btceth=bc_div($btcClose,$ethClose);
        $ethbtc=bc_div($ethClose,$btcClose);
        $jo["btc_eth"]=$btceth;
        $jo["eth_btc"]=$ethbtc;

        $usdt_eth=bc_div(1,$ethClose);
        $usdt_btc=bc_div(1,$btcClose);

        $jo["usdt_eth"]=$usdt_eth;
        $jo["usdt_btc"]=$usdt_btc;

        $user_id = Users::getUserId();
        $fromCoin=$request->input('fromCoin');
        $toCoin=$request->input('toCoin');

        $fromAmount=$request->input('fromAmount');
        //检查fromCoin资产数量
        $us = DB::table('currency')->where('name', $fromCoin)->first();
        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        $balance= isset($wal->legal_balance) ? $wal->legal_balance : '0.00';
        //目标钱包
        $usB = DB::table('currency')->where('name', $toCoin)->first();
        $walB = UsersWallet::where('currency', $usB->id)->where('user_id', $user_id)->first();

        if ($fromAmount>$balance){
            $j["msg"]=trans('currency.zczhyebz')."==".$balance;
            return $this->success($j);
        }else{
            //
            if ($fromCoin=="USDT"&&$toCoin=="BTC"){
                $coinBAmount=$usdt_btc*$fromAmount;
            }
            if ($fromCoin=="USDT"&&$toCoin=="ETH"){
                $coinBAmount=$usdt_eth*$fromAmount;
            }
            if ($fromCoin=="USDT"&&$toCoin=="USDC"){
                $coinBAmount=$fromAmount;
            }
            
            if ($fromCoin=="BTC"&&$toCoin=="ETH"){
                  $coinBAmount=$btceth*$fromAmount;
            }
            if ($fromCoin=="BTC"&&$toCoin=="USDT"){
                $coinBAmount=$btcClose*$fromAmount;
            }
            if ($fromCoin=="BTC"&&$toCoin=="USDC"){
                $coinBAmount=$btcClose*$fromAmount;
            }
            
            if ($fromCoin=="ETH"&&$toCoin=="BTC"){
                $coinBAmount=$ethbtc*$fromAmount;
            }
            if ($fromCoin=="ETH"&&$toCoin=="USDT"){
                $coinBAmount=$ethClose*$fromAmount;
            }
            if ($fromCoin=="ETH"&&$toCoin=="USDC"){
                $coinBAmount=$ethClose*$fromAmount;
            }
            
            if ($fromCoin=="USDC"&&$toCoin=="BTC"){
                $coinBAmount=$usdt_btc*$fromAmount;
            }
            if ($fromCoin=="USDC"&&$toCoin=="ETH"){
                $coinBAmount=$usdt_eth*$fromAmount;
            }
            if ($fromCoin=="USDC"&&$toCoin=="USDT"){
                $coinBAmount=$fromAmount;
            }
           try {
            change_wallet_balance($wal, 0, -$fromAmount, AccountLog::LOCK_MINING, '资产闪兑扣除资产','Asset flash redemption deduction of assets');
            $this->changeWalletBalance($walB, 0, $coinBAmount, AccountLog::LOCK_MINING, '资产闪兑增加资产','Asset flash trading increases assets',false);
           } catch (\Exception $e) {
              return $this->error($e->getMessage());
           }
            $j["msg"]=trans('currency.dhcg');
            return $this->success($j);

        }

    }
    function curl($url,$postdata=[]) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $output;
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
            if (!in_array($balance_type, [1, 2, 3,4,0])) {
                throw new \Exception('货币类型不正确');
            }
            DB::transaction(function () use (&$wallet, $param) {
                extract($param);
                $fields = [
                    'legal_balance',//资金账户
                    'change_balance',//币币账户
                    'lever_balance',//合约账户
                    'micro_balance',//秒合约
                    'earn_balance'//理财
                ];

                $field ='legal_balance';
                $wallet = $wallet->lockForUpdate()->findOrFail($wallet->getKey()); //钱包获取最新钱包数据并锁定
                $user_id = $wallet->user_id;
                $before = $wallet->$field;

                $after = bc_add($before, $change);

                //判断余额是否充足
                if (bc_comp($after, '0') < 0 && !$overflow) {
                    //throw new \Exception('Wallet ' . $field . ' not sufficient funds');
                    throw new \Exception(trans('microorder.wallet') .' 2 '.$field.trans('microorder.yezjbz'));

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
                    throw new \Exception('The wallet change balance is abnormal');
                }
            });
            return true;
        } catch (\Exception $e) {
            throw $e;
           if($memo == '资产闪兑增加资产') {
                return $e->getMessage();
            }else {
                 return false;
            }
        } finally {
            AccountLog::reguard();
            WalletLog::reguard();
        }
    }

}
