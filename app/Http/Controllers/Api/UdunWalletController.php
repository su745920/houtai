<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Mongodb\UdunTrade;
use App\Models\Withdraw;
use Illuminate\Http\Request;

class UdunWalletController extends ApiController
{

    // 优盾钱包回调
    public function notify(Request $request)
    {
        $res = $request->all();

        if(blank($res)){
            info('=====优盾钱包回调通知验签失败1======',$res);
            return;
        }

        // 先验签
        $sign = md5($res['body'] . config('coin.api_key','a0cd2e9a2a50b2682b198b5f6ff9b9b0') . $res['nonce'] . $res['timestamp']);
        if($res['sign'] != $sign){
            info('=====优盾钱包回调通知验签失败2======',$res);
        }
        $trade = json_decode($res['body'],true);

        //TODO 业务处理
        if($trade['tradeType'] == 1){
            info('=====收到充币通知======',$trade);

            if($trade['status'] == 3){
                // 交易成功
                \App\Jobs\UdunDeposit::dispatch($trade)->onQueue('UdunDeposit'); // 充值
            }
        }elseif ($trade['tradeType'] == 2){
            info('=====收到提币通知======',$trade);

            $withdrawId = str_before($trade['businessId'],'-');
            $withdraw = Withdraw::query()->find($withdrawId);
            if(blank($withdraw)){
                info('===优盾钱包提币记录找不到===');
            }else{
                if($trade['status'] == 1){
                    // 审核通过，转账中
                    $withdraw->update(['status'=>Withdraw::status_pass]);
                }elseif ($trade['status'] == 2){
                    // 审核不通过
                    $withdraw->update(['status'=>Withdraw::status_reject]);
                }elseif ($trade['status'] == 3){
                    // 提币已到账
                    $withdraw->update(['status'=>Withdraw::status_success]);
                }elseif ($trade['status'] == 4){
                    // 交易失败
                    $withdraw->update(['status'=>Withdraw::status_failed]);
                }
            }
        }

        return "success";
    }
}
