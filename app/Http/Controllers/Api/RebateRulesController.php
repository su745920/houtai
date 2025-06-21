<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\Models\{
    RebateRules};
use App\Events\WithdrawSubmitEvent;
use App;
use Illuminate\Support\Facades\Redis;

class RebateRulesController extends Controller
{
    public function index(){
        $lang = request()->input('lang','en');

        $list = RebateRules::where('lang',$lang)->get();

        return $this->success($list);
    }

}