<?php

namespace App\Http\Controllers\Admin;

use App\Models\AccountLog;
use App\Models\CzRewardGroup;
use Illuminate\Http\Request;

class AdminTranFeeRewardController extends Controller
{
    public function index(){
        $authorityList=session()->get("authorityList");
        return view("admin.tran_fee_reward.index", [

            'authorityList' =>$authorityList
        ]);
    }
    public function page(Request $request)
    {
        $limit = $request->input('limit', 10);
        $account_number = $request->input('account_number', '');
        $status = $request->input('status','');

        $rewardGroup = AccountLog::where("id", '>', 0);

        $rewardGroup = $rewardGroup->whereNotNull('tx_amount');




        $total = 0;
        if("0"==$status){
            $rewardGroup = $rewardGroup->where('status', 0);
        }
        if("1"==$status){
            $rewardGroup = $rewardGroup->where('status', 1);
        }
        if("2"==$status){
            $rewardGroup = $rewardGroup->where('status', 2);
        }
        if (!empty($account_number)){
            $rewardGroup = $rewardGroup->where('reward_username','like', '%'.$account_number.'%');
        }

        $start_time = $request->input('start_time', '');
        $end_time = $request->input('end_time', '');
        if (!empty($start_time)){

            $rewardGroup=$rewardGroup->where('create_date', '>=', $start_time);
        }
        if (!empty($end_time)){

            $rewardGroup=$rewardGroup->where('create_date', '<=', $end_time);
        }


        $list = $rewardGroup->orderBy('status', 'asc')->orderBy('create_date', 'desc')->paginate($limit);
//        foreach ($list as $vo){
////            $country_code=$vo->country_code;
////            if (empty($country_code)||"undefined"==$country_code){
////                $vo->country_code="";
////            }
//        }
        $items = $list->items();



        $total = $list->total();
        return response()->json(['code' => 0, 'data' => $items, 'count' => $total]);
    }

}