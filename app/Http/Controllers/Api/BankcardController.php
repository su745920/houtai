<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\{News, NewsCategory, Users};
use App\Models\Bankcard;
use App;
class BankcardController extends Controller
{
    public function saveBankcard(Request $request)
    {
        $uid = Users::getUserId();
        $cnt=Bankcard::where('uid', $uid)->count();
        if (!empty($cnt)&&$cnt>0){
            //20221127 须配合前端改
            return $this->success(trans('user.exceed_1'));
        }


        $po = new Bankcard();

        $truename = $request->input('truename');
        if (!empty($truename)&&strlen($truename)>0){
            $po->truename = $truename;
        }


        $bankcardno = $request->input('bankcardno');
        if (!empty($bankcardno)&&strlen($bankcardno)>0){
            $po->bankcardno = $bankcardno;
        }



        $po->uid = $uid;



        $bankname=$request->input('bankname');
        if (!empty($bankname)&&strlen($bankname)>0){
            $po->bankname=$bankname;
        }
        $provincecity=$request->input('provincecity');
        if (!empty($provincecity)&&strlen($provincecity)>0){
            $po->provincecity=$provincecity;
        }

        $po->banktype=0;




        try {
            $po->save();
            return $this->success(trans('user.bccg'));
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

    }

    public function getList(Request $request)
    {
        $limit = $request->get('limit', 15);
        $page = $request->get('page', 1);
        $uid = Users::getUserId();

        $article = Bankcard::where('uid', $uid)
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return $this->success([
            "list" => $article->items(),
            'count' => $article->total(),
            "page" => $page,
            "limit" => $limit
        ]);
    }

    public function saveBankcard2(Request $request)
    {
        $uid = Users::getUserId();
        $cnt=Bankcard::where('uid', $uid)->count();
        if (!empty($cnt)&&$cnt>0){
            //20221127 须配合前端改
            return $this->success(trans('user.exceed_1'));
        }

        $po = new Bankcard();

        $truename = $request->input('truename');
        if (!empty($truename)&&strlen($truename)>0){
            $po->truename = $truename;
        }
        $bankcardno = $request->input('bankcardno');
        if (!empty($bankcardno)&&strlen($bankcardno)>0){
            $po->bankcardno = $bankcardno;
        }
       
        $po->uid = $uid;
        $bankname=$request->input('bankname');
        if (!empty($bankname)&&strlen($bankname)>0){
            $po->bankname=$bankname;
        }
        $provincecity=$request->input('provincecity');
        if (!empty($provincecity)&&strlen($provincecity)>0){
            $po->provincecity=$provincecity;
        }
        //
        $store=$request->input('store');
        if (!empty($store)&&strlen($store)>0){
            $po->store=$store;
        }
        $idcard=$request->input("idcard");
        if (!empty($idcard)&&strlen($idcard)>0){
            $po->idcard=$idcard;
        }
        $international_code=$request->input('international_code');
        if (!empty($international_code)&&strlen($international_code)>0){
            $po->international_code=$international_code;
        }
        $link_mp=$request->input('link_mp');
        if (!empty($link_mp)&&strlen($link_mp)>0){
            $po->link_mp=$link_mp;
        }
        $po->banktype=1;



        try {
            $po->save();
            return $this->success(trans('user.bccg'));
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

    }

}
