<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\Models\{Currency,
    Address,
    AccountLog,
    Setting,
    Users,
    UsersWallet,
    UsersWalletOut,
    LeverTransaction,
    CurrencyQuotation,
    WalletSettingV2};
use App\Events\WithdrawSubmitEvent;
use App;
use Illuminate\Support\Facades\Redis;

class RechargeDataV2Controller extends Controller
{
    public function index(){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $po = WalletSettingV2::find(1);

        return $this->success($po);
    }

}
