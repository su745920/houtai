<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use App\Models\MicroNumber;
use App\Models\MicroOrder;
use App\Models\MicroAmount;
use App\Models\Currency;
use App\Models\CurrencyMatch;
use App\Models\Setting;
use App\Models\UsersWallet;
use App\Models\Users;

class AdminMicroAmountController extends Controller
{
    public function index()
    {
        $authorityList=session()->get("authorityList");
        return view('admin.micro_amount.index',['authorityList' =>$authorityList]);

    }

    public function amountListAjax(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new MicroAmount();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }
    public function amount_edit(){
        $authorityList=session()->get("authorityList");
        return view('admin.micro_amount.amount_edit',['authorityList' =>$authorityList]);
    }
    //amount_add.blade.php
    public function amount_add(){
        $authorityList=session()->get("authorityList");
        return view('admin.micro_amount.amount_add',['authorityList' =>$authorityList]);
    }
    public function saveMicroAmount(Request $request){
        $amount=$request->input("amount");

        $po = new MicroAmount();
        $po->amount=$amount;
        $po->save();

        return $this->success("保存成功");
    }
    public function  updateMicroAmount(Request $request){
        $amount=$request->input("amount");

        $po = new MicroAmount();
        $po->amount=$amount;
        $po->save();

        return $this->success("保存成功");
    }

}