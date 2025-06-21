<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{AccountLog, LeverTransaction, Users, Setting, WalletSetting};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App;
use Illuminate\Support\Facades\Log;
class HqPushController extends Controller
{
    public function handleAction(Request $request)
    {

        $currency_id=$request->input("currency_id");
        $now_price=$request->input("now_price");
        $now=time();

        //价格大于0才做更新处理
        if (bc_comp($now_price, '0') > 0) {
            Log::info("价格变动");

            LeverTransaction::newPrice(23, $currency_id, $now_price, $now);
        }
    }
}
