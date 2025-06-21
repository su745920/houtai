<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FromQueryExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{Address,
    AccountLog,
    Bankcard,
    Currency,
    Follow,
    PaymentMethod,
    Users,
    Hq1min,Hq5min,Hq15min,Hq30min,Hq60min, Hq1day,Hq1week,Hq1mon,
    LeverTransaction,
    LockMiningOrder,
    MicroOrder,
    RechargeRecord,
    WalletLog,
    MarketHour,
    CreditLog};

class AdminHqHistoryController extends Controller
{
    public function page()
    {
        $currencies =  Currency::where('is_display', '1', 1)->where('name','!=','USDT')->orderBy('sort','desc')->get();
        $authorityList=session()->get("authorityList");
        return view("admin.hq_control.page",['authorityList' =>$authorityList])->with('currencies', $currencies);
    }
    public function historyListAjax(Request $request)
    {
        $symbol=$request->input("symbol",'BTC');
        $symbol = strtolower($symbol);
        $symbol = $symbol."usdt";
        $list = Hq1min::query()->where(['control'=> 1,'symbol' => $symbol])->orderBy('id','desc')->paginate();
        
        if (!empty($list)){
            foreach ($list as $item){
                $id=$item->id;
                $date=date("Y-m-d H:i",$id);
                $item->date=$date;
            }
        }
        
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
    // 删除插针
    public function historyDelete(Request $request) {
        $id=$request->input("id",-1);
        $data = Hq1min::query()->where(['id'=> $id])->delete();
        Hq1min::query()->where(['id'=> $id])->delete();
        Hq5min::query()->where(['id'=> $id])->delete();
        Hq15min::query()->where(['id'=> $id])->delete();
        Hq30min::query()->where(['id'=> $id])->delete();
        Hq60min::query()->where(['id'=> $id])->delete();
        Hq1day::query()->where(['id'=> $id])->delete();
        Hq1week::query()->where(['id'=> $id])->delete();
        Hq1mon::query()->where(['id'=> $id])->delete();
        if($data) {
            return $this->success("删除成功");
        }else {
            return $this->error("删除失败");
        }
    }

}
