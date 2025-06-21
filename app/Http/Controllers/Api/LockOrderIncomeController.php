<?php

namespace App\Http\Controllers\Api;

use App\Models\InsuranceClaimApply;
use App\Models\InsuranceRule;
use App\Models\LockMining;
use App\Models\RewardGroup;
use App\Models\Setting;
use App\Models\UsersInsurance;
use Carbon\Carbon;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Logic\MicroTradeLogic;
use App\Models\Users;
use App\Models\CurrencyQuotation;
use App\Models\Currency;
use App\Models\MicroSecond;
use App\Models\UsersWallet;
use App\Models\LockMiningOrder;
use App\Models\MarketHour;
use App\Models\CurrencyMatch;
use App\Models\InsuranceType;
use App\Models\MicroNumbers;

use App\Models\AccountLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App;

class LockOrderIncomeController extends Controller
{
    public function logs(Request $request)
    {
        $orderId = $request->input('orderId', 1);
        $po = LockMiningOrder::where('id', $orderId)->first();
        $created_at =$po->created_at;
        $money = $po->money;

        $lock_id = $po->lock_id;
        $product = LockMining::where('id', $lock_id)->first();//产品
        $day = $product->day;
        $rate_max = $product->rate_max;
        $daylong = intval($day) * 86400;//项目总的持续秒数
        $maxShouyi = $rate_max * 0.01 * $money;//每天收益
        $ljShouyi = 0;

        $created_at=strtotime($created_at);

        $status=$po->status;
        $l=$created_at + $daylong;
        if ($l >= time() && $status == 0) {
            $cha = time() - $created_at;
            $reallyDays = ceil(intval($cha) / 86400);

            //echo '一天之前的时间为：'.date('Y-m-d',$created_at+24*3600);
            $logs = array();
            for ($i = 0; $i < $reallyDays; $i++) {
                $vo["day"] = date('Y-m-d', $created_at + 24 * 3600 * $i);
                $vo["income"] = $maxShouyi;
                array_push($logs,$vo);
            }


            return $this->success($logs);


        } else {


            $logs = array();
            for ($i = 0; $i < $day; $i++) {
                $vo["day"] = date('Y-m-d', $created_at + 24 * 3600 * $i);
                $vo["income"] = $maxShouyi;
                array_push($logs,$vo);
            }

            return $this->success($logs);

        }


    }

}