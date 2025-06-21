<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{UserCollection, Users, Setting};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App;

class UserCollectionController extends Controller
{
    public function myhqCollect(Request $request){
        $uid = Users::getUserId();
        $list=UserCollection::where('uid', "=", $uid)->get()->toArray();
        return $this->success($list);
    }
    
    public function isCollect(Request $request){
        $uid = Users::getUserId();
        $currency_id=$request->input('currency_id');
        $po=UserCollection::where('uid', "=", $uid)->where('currency_id',$currency_id)->first();
        if (!empty($po)){
            return $this->success('1');
        }else{
            return $this->success('0');
        }

    }
    
    public function saveCollection(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        try {
        $uid = Users::getUserId();
        $currency_id=$request->input('currency_id');
        $po=[
            'uid'=>$uid,
            'currency_id'=>$currency_id
        ];
       $res=null;
        if ($currency_id>0){
            $res = DB::table('user_collection')->insert($po);
        }
        if ($res) return $this->success('success');else return $this->error('fail');
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }
    public function delCollection(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        try {
            $uid = Users::getUserId();
            $currency_id=$request->input('currency_id');
            $res=UserCollection::where('uid', "=", $uid)->where('currency_id',$currency_id)->delete();
            if ($res) return $this->success('success');else return $this->error('fail');
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

}