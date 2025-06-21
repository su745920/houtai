<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\{AppApi,
    UserChat,
    Users,
    UserReal,
    Token,
    AccountLog,
    UsersWallet,
    Currency,
    InviteBg,
    Setting,
    UserCashInfo,
    ExchangeShiftTo};
use GuzzleHttp\Client;
use App;

class TeamController extends Controller
{
    public function index()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $uid = Users::getUserId();
        $teamCount = 0;
        //1级
        $list1 = Users::where('parent_id', $uid)->get();
        $list2 = [];
        $list3 = [];
        //substr($str,0,strlen($str)-1)
        $uidStr="'".$uid."',";
        if (!empty($list1)) {
            $teamCount = $teamCount + count($list1);
            foreach ($list1 as $u1) {
                $uid2 = $u1->id;
                $uidStr=$uidStr."'".$uid2."',";
                $list2 = Users::where('parent_id', $uid2)->get();
                if (!empty($list2)) {
                    $teamCount = $teamCount + count($list2);
                    foreach ($list2 as $u2) {
                        $uid3 = $u2->id;
                        $uidStr=$uidStr."'".$uid3."',";

                        $list3 = Users::where('parent_id', $uid3)->get();
                        if (!empty($list3)) {
                            $teamCount = $teamCount + count($list3);
                            foreach ($list3 as $u3){
                                $uid4 = $u3->id;
                                $uidStr=$uidStr."'".$uid4."',";
                            }
                        }
                    }
                }

            }
        }
        $uidStr=substr($uidStr,0,strlen($uidStr)-1);
        //$user=DB::select('select  sum(money) as allCZ from recharge_record where user_id in ('.$uidStr.')');
//        return $this->success(array(
//                'teamCount' => $teamCount, 'list1' => $list1, 'list2' => $list2, 'list3' => $list3,'allCZ'=>$user[0]->allCZ
//            ));

        $ls=DB::select('select  sum(value) as allLS from account_log where user_id in ('.$uidStr.')');
        $allLS=$ls[0]->allLS;
        //
        $firstCzCnt=DB::select('select count(DISTINCT user_id) as firstcnt from recharge_record where user_id in ('.$uidStr.')');
        $firstCZCnt=$firstCzCnt[0]->firstcnt;

        $jyUsercntArr=DB::select('select count(DISTINCT user_id) as jyUsercnt from account_log where user_id in ('.$uidStr.')');
        $jyUsercnt=$jyUsercntArr[0]->jyUsercnt;

        return $this->success(array(
            'teamCount' => $teamCount, 'list1' => $list1, 'list2' => $list2, 'list3' => $list3,'allLS'=>$allLS,'firstCZCnt'=>$firstCZCnt,'jyUsercnt'=>$jyUsercnt
        ));

    }

}