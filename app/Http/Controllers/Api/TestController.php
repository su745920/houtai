<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\{AccountLog, CurrencyMatch, LeverTransaction, LeverMultiple, Setting, TransactionComplete, TransactionIn, TransactionOut, Users, UsersWallet};
use App\Events\LeverSubmitOrderEvent;
use App\Jobs\LeverClose;
use App;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{

    //https://bicnvip.com/getBinancePrice?coinId=32
    public function getBinancePrice(Request $request){
        $coinId=$request->input("coinId");
        $result = file_get_contents('https://hq.bicnvip.com/market/binance/redis/getLatestQuotations');
        $array = json_decode($result, true);
        $data=$array["data"];
        foreach ($data as $po){
            $id=$po['id'];
            if ($coinId == $id){
                return  $po['close'];
            }
        }
        return "";




    }


}