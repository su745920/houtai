<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\{News, NewsCategory, Users};
use App\Models\PaymentMethod;
use App;
class PaymentMethodController extends Controller
{
    public function savePaymentMethod(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $po = new PaymentMethod();
        $coin = $request->input('coin', '');
        $uid = Users::getUserId();
        //判断唯一性
        if ($coin=="USDT"){
            $lianType=$request->input('lianType',0);
            $po->lianType=$lianType;
            $list = PaymentMethod::where('uid', $uid)->where('coin', $coin)->where('lianType', $lianType)->get();
            if (count($list) > 0) {
                //return $this->success("coin_exists");
            }
        }else {
            $list = PaymentMethod::where('uid', $uid)->where('coin', $coin)->get();
            if (count($list) > 0) {
                //return $this->success("coin_exists");
            }
        }
        $po->coin = $coin;
        $address = $request->input('address');
        $po->address = $address;
        $po->uid = $uid;
        $po->create_time = date('yyyy-MM-dd HH:mm');
        $upload_pic=$request->input('upload_pic');
        if (!empty($upload_pic)&&strlen($upload_pic)>0){
            $po->upload_pic=$upload_pic;
        }

        try {
            $po->save();
            return $this->success(trans("wallet.tjtbdzcg"));
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

    }
    // 编辑
     public function editPaymentMethod(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $coin = $request->input('coin', '');
        $uid = Users::getUserId();
        $id = $request->input('id',0);
        $po = PaymentMethod::where('uid', $uid)->where('id',$id)->first();
        if(empty($po)) {
            return $this->error(trans('wallet.ctbdzbcz'));
         }
        //判断唯一性
        if ($coin=="USDT"){
            $lianType=$request->input('lianType',0);
            $po->lianType=$lianType;
            $list = PaymentMethod::where('uid', $uid)->where('coin', $coin)->where('lianType', $lianType)->get();
            if (count($list) > 0) {
                //return $this->success("coin_exists");
            }
        }else {
            $list = PaymentMethod::where('uid', $uid)->where('coin', $coin)->get();
            if (count($list) > 0) {
                //return $this->success("coin_exists");
            }
        }
        $po->coin = $coin;
        $address = $request->input('address');
        $po->address = $address;
        $upload_pic=$request->input('upload_pic');
        if (!empty($upload_pic)&&strlen($upload_pic)>0){
            $po->upload_pic=$upload_pic;
        }

        try {
            $po->save();
            return $this->success(trans('wallet.czcg'));
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    // 详情
    public function getPaymentMethod(Request $request) {
         $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $uid = Users::getUserId();
        $id = $request->input('id',0);
        
        $data = PaymentMethod::where('uid', $uid)->where('id',$id)->first();
        if($data) {
           return $this->success($data);
       }else {
           return $this->error(trans('wallet.ctbdzbcz'));
       }
    }
    // 删除
     public function delPaymentMethod(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $uid = Users::getUserId();
        $id = $request->input('id',0);
       
       try {
           $po = PaymentMethod::where('uid', $uid)->where('id',$id)->first();
           if($po) {
               PaymentMethod::where('uid', $uid)->where('id',$id)->delete();
               return $this->success(trans('wallet.sctbdzcg'));
           }else {
               return $this->error(trans('wallet.ctbdzbcz'));
           }
            
       }catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function getList(Request $request)
    {
        $limit = $request->get('limit', 15);
        $page = $request->get('page', 1);
        $uid = Users::getUserId();
        $coin = $request->input('coin', '');
        $article=null;
        if (!empty($coin)&&("BTC"==$coin||"USDT(TRC20)"==$coin||"ETH"==$coin||"USDT(ERC20)"==$coin)){
            $article = PaymentMethod::where('uid', $uid)->where('coin',$coin)
                ->orderBy('id', 'desc')
                ->paginate($limit, ['*'], 'page', $page);
        }else{

            $article = PaymentMethod::where('uid', $uid)
                ->orderBy('id', 'desc')
                ->paginate($limit, ['*'], 'page', $page);
        }

        //
        foreach ($article->items() as $vo){
            $coin=$vo->coin;
            if ($coin=="USDT"){
                $lianType=$vo->lianType;
                if ($lianType==0){
                    $vo->lianTypeZH="ERC20";
                }
                if ($lianType==1){
                    $vo->lianTypeZH="TRC20";
                }
                if ($lianType==2){
                    $vo->lianTypeZH="OMNI";
                }
            }
        }


        return $this->success([
            "list" => $article->items(),
            'count' => $article->total(),
            "page" => $page,
            "limit" => $limit
        ]);
    }

}
