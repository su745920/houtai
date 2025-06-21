<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{AccountLog, CzRewardGroup, Users, Setting, UsersWallet};
use Illuminate\Support\Facades\DB;

class CzRewardGroupController extends Controller
{
    public function reject(Request $request){
        $audit_date = date("Y-m-d H:i");
        $audit_username=session()->get("admin_username");
        $audit_uid=session()->get("admin_id");
        $id=$request->input("id");
        $sql="update cz_reward_group set `status`=2,audit_date='".$audit_date."',audit_uid='".$audit_uid."',audit_username='".$audit_username."'  where id=".$id;
        DB::update($sql);
        return $this->success('拒绝成功');
    }
    public function tg(Request $request){
        $id=$request->input("id");
        $po=CzRewardGroup::find($id);

        $currency = 23;
        $amount=$po->reward_amount;
        $user_id=$po->parent_uid;


        $wallet = UsersWallet::where('currency', $currency)->where('user_id', $user_id)->first();
        DB::beginTransaction();
        try {
            $result = change_wallet_balance($wallet,
                0,
                +$amount,
                AccountLog::WALLET_CURRENCY_IN,
                '后台奖励充币',
                'Backend reward deposit');
            if ($result !== true) {
                throw new \Exception($result);
            }
            //更新审核状态为通过 1 2：拒绝
            $audit_date = date("Y-m-d H:i");
            $audit_username=session()->get("admin_username");
            $audit_uid=session()->get("admin_id");
            $sql="update cz_reward_group set `status`=1,audit_date='".$audit_date."',audit_uid='".$audit_uid."',audit_username='".$audit_username."'  where id=".$id;
            DB::update($sql);

            DB::commit();

            return $this->success('通过成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function index(){
        $authorityList=session()->get("authorityList");
        return view("admin.cz_reward_group.index", [

            'authorityList' =>$authorityList
        ]);
    }
    public function page(Request $request)
    {
        $limit = $request->input('limit', 10);
        $account_number = $request->input('account_number', '');
        $status = $request->input('status','');

        $rewardGroup = CzRewardGroup::where("id", '>', 0);
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
        foreach ($list as $vo){
//            $country_code=$vo->country_code;
//            if (empty($country_code)||"undefined"==$country_code){
//                $vo->country_code="";
//            }
        }
        $items = $list->items();
        $total = $list->total();
        return response()->json(['code' => 0, 'data' => $items, 'count' => $total]);
    }

}
