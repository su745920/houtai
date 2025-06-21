<?php

namespace App\Http\Controllers\Api;
use App\Models\StockNews;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockDistribution;
class StockCompanyController extends Controller
{
    public function news(Request $request){
        $code=$request->input("code");

        $distributionList = StockNews::where('code', $code)
            ->orderBy('id', 'asc')->get();

        return $this->success($distributionList);
    }
    //简况
    public function overview(Request $request){
        $code=$request->input("code");
        $po = DB::table('stock_company')->where('code', $code)->first();

        $distributionList = StockDistribution::where('code', $code)
            ->orderBy('id', 'asc')->get();

        $jo["company"]=$po;
        $jo["distributionList"]=$distributionList;

        return $this->success($jo);
    }

}