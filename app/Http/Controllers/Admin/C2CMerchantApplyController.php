<?php

namespace App\Http\Controllers\Admin;

use App\Models\AccountLog;
use App\Models\C2cAppeal;
use App\Models\RechargeRecord;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\UsersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class C2CMerchantApplyController extends Controller
{
    public function applyPageAjax(Request $request)
    {
        $limit = $request->input('limit', 10);
        $account_number = $request->input('account_number', '');
        $seller_number = $request->input('seller_number', '');
        $type = $request->input('type', '');
        $is_sure = $request->input('is_sure', '');
        // $currency_id = $request->input('currency_id', 0);
        $result = new Seller();
        if (!empty($account_number)) {
            $result = $result->whereHas('user', function ($query) use ($account_number) {
                $query->where('account_number', 'like', '%' . $account_number . '%');
            });
        }


        $start_time = $request->input('start_time', '');
        $end_time = $request->input('end_time', '');
        if (!empty($start_time)){
            //$start_time=strtotime($start_time);
            $result=$result->where('create_date', '>=', $start_time);//DATE_FORMAT(create_date,'%Y-%m-%d')
        }
        if (!empty($end_time)){
            //$end_time=strtotime($end_time);
            $result=$result->where('create_date', '<=', $end_time);
        }

        $result=$result->where('audit_status', '=', 0);

        $result = $result->orderBy('id', 'desc')->paginate($limit);

        foreach ($result as $po){
            $create_time=$po->create_time;

            //$po->createTimeStr= date('Y-m-d H:i', $create_time);
        }



        $sum=$result->sum('number');
        return $this->layuiData($result,$sum);
    }

    public function apply_page()
    {
        $authorityList=session()->get("authorityList");
        return view('admin.c2c_merchant.merchant_apply',['authorityList' =>$authorityList]);
        //appeal.blade.php
    }



    public function merchant_success(Request $request)
    {
        $id = $request->input('id');
        $po = Seller::find($id);
        $user_id = $po->user_id;
        //$po->update(['audit_status' => 1]);
        $sql= "update seller set audit_status=1 where  id=".$id;
        DB::update($sql);

        return $this->success("操作成功");
    }

    public function merchant_reject(Request $request)
    {
        $id = $request->input('id');
        Seller::find($id)->update(['status' => 2]);



        return $this->success("操作成功");
    }

}