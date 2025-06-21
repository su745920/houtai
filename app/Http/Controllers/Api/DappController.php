<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Utils\RPC;
use App\Models\{AppVersion, Bank, Setting, Token, Users,UsersWallet,MarketHour,Menu,Menu2};
use App\DAO\UploaderDAO;
use App\Jobs\{LeverUpdate, SendMarket};
use App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class DappController extends Controller
{
    //质押币种列表
    public function pledgeCoinList()
    {
        $list = App\Models\Currency::whereIn('id',[61,62])->orderBy('sort','asc')->get();
        return $this->success($list);
    }

}