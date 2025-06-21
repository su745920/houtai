<?php

namespace App\Http\Controllers\Api;

use App\Models\C2cMsg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AccountLog;
use App\Models\Currency;
use App\Models\C2CUserOrder;
use App\Models\C2CInfo;
use App\Models\Setting;
use App\Models\Users;
use App\Models\UsersWallet;
use App\Models\UserReal;
use App\Models\UserCashInfo;
use App;


class C2cMsgController extends Controller
{
    //订单详情--点击右上角消息
    public function msgList(){
        $currentUID = Users::getUserId();
        $list=C2cMsg::where("to_uid",$currentUID)->orderBy('id', 'desc')->paginate(20);
        return $this->success($list);
    }

}
