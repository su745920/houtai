<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{AccountLog, Users, Setting, UsersWallet};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App;

class HuobiController extends Controller
{
    //    https://gdmall.xyz/api/hb/hq
    public function hq(){
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

        $usdcClose=1;
        $usdClose=1;
        $cnyClose=0.14;

        $jo["btcPrice"]=$btcClose;
        $jo["ethPrice"]=$ethClose;
        $jo["usdcPrice"]=$usdcClose;
        $jo["usdPrice"]=$usdClose;
        $jo["cnyPrice"]=$cnyClose;

        $btceth=bc_div($btcClose,$ethClose);
        $ethbtc=bc_div($ethClose,$btcClose);

        $jo["btc_eth"]=$btceth;
        $jo["eth_btc"]=$ethbtc;

        $usdt_eth=bc_div(1,$ethClose);
        $usdt_btc=bc_div(1,$btcClose);
        $usdt_usdc=bc_div(1,$usdcClose);
        $usdt_usd = 1;
        $usdt_cny = bc_div(1,$cnyClose);

        $jo["usdt_eth"]=$usdt_eth;
        $jo["usdt_btc"]=$usdt_btc;
        $jo["usdt_usdc"]=$usdt_usdc;
        $jo["usdt_usd"]=$usdt_usd;
        $jo["usdt_cny"]=$usdt_cny;
        
        $user_id = Users::getUserId();
        $us = DB::table('currency')->where('name',"USDT")->first();
        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        $usdtBalance= isset($wal->lever_balance) ? $wal->lever_balance : '0.00';


        $jo["usdtBalance"]=$usdtBalance;

        $us = DB::table('currency')->where('name',"BTC")->first();
        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        $btcBalance= isset($wal->lever_balance) ? $wal->lever_balance : '0.00';

        $jo["btcBalance"]=$btcBalance;

        $us = DB::table('currency')->where('name',"ETH")->first();
        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        $ethBalance= isset($wal->lever_balance) ? $wal->lever_balance : '0.00';
        $jo["ethBalance"]=$ethBalance;

        $us = DB::table('currency')->where('name',"USDC")->first();
        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        $ethBalance= isset($wal->lever_balance) ? $wal->lever_balance : '0.00';
        $jo["usdcBalance"]=$ethBalance;

        $us = DB::table('currency')->where('name',"USD")->first();
        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        $ethBalance= isset($wal->lever_balance) ? $wal->lever_balance : '0.00';
        $jo["usdBalance"]=$ethBalance;

        $us = DB::table('currency')->where('name',"CNY")->first();
        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();
        $ethBalance= isset($wal->lever_balance) ? $wal->lever_balance : '0.00';
        $jo["cnyBalance"]=$ethBalance;


        return $this->success($jo);


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

}