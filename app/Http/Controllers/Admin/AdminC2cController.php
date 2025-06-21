<?php

namespace App\Http\Controllers\Admin;

use App\Models\C2cAppeal;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\C2CUserOrder;
use App\Models\Currency;

class AdminC2cController extends Controller
{
    public function appealPageData(Request $request)
    {
        $limit = $request->input('limit', 10);
        $account_number = $request->input('account_number', '');
        $seller_number = $request->input('seller_number', '');
        $type = $request->input('type', '');
        $is_sure = $request->input('is_sure', '');
        // $currency_id = $request->input('currency_id', 0);
        $result = new C2cAppeal();
        if (!empty($account_number)) {
            $result = $result->whereHas('user', function ($query) use ($account_number) {
                $query->where('account_number', 'like', '%' . $account_number . '%');
            });
        }

        if (!empty($seller_number)) {

            $result = $result->whereHas('seller', function ($query) use ($seller_number) {
                $query->where('account_number', 'like', '%' . $seller_number . '%');
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


        $result = $result->orderBy('id', 'desc')->paginate($limit);

        $sum=$result->sum('number');
        return $this->layuiData($result,$sum);
    }

    public function appeal()
    {
        return view('admin.c2c.appeal');
        //appeal.blade.php
    }

}
