<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use App\Models\Hq15min;
use App\Models\Hq1min;
use App\Models\Hq30;
use App\Models\Hq5;
use App\Models\InsuranceType;
use App\Models\UsersInsurance;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Hq5Controller extends Controller
{

    //   https://mywordpro.xyz/api/updateHq5

    public function updateHq(){
        $t=1689865800;
        $date=date("Y-m-d H:i:s",$t);
        echo $date;
    }


    public function findKline(){

    }

    //   https://mywordpro.xyz/api/saveHq
    public function  saveHq()
    {
        //$symbolList=["btc","eth","dot","shib","ada","doge","fil","ltc","lrc","ht","etc","trx","xrp"];
        $symbolList=["btc","eth","shib","trx"];
        foreach ($symbolList as $symbol){
            $url="https://api.huobi.pro/market/history/kline?period=1min&size=200&symbol=".$symbol."usdt";
            $res = file_get_contents($url);
            $res = json_decode($res, true);
            $data=$res['data'];



            foreach ($data as $item){
                $id=$item['id'];
                $sql="select  * from hq1min t where  t.symbol='".$symbol."usdt'  and  id=".$id;
                $tmpList = DB::select($sql);
                if (empty($tmpList)||count($tmpList) ==0){
                    $hq=new Hq1min();
                    $hq->id=$id;
                    $hq->close=$item['close'];
                    $hq->open=$item['open'];
                    $hq->amount=$item['amount'];
                    $hq->low=$item['low'];
                    $hq->vol=$item['vol'];
                    $hq->count=$item['count'];
                    $hq->high=$item['high'];
                    $hq->symbol=$symbol."usdt";
                    $hq->save();
                    if ($id%300 ==0 ){
                        $hq=new Hq5();
                        $hq->id=$id;
                        $hq->close=$item['close'];
                        $hq->open=$item['open'];
                        $hq->amount=$item['amount'];
                        $hq->low=$item['low'];
                        $hq->vol=$item['vol'];
                        $hq->count=$item['count'];
                        $hq->high=$item['high'];
                        $hq->symbol=$symbol."usdt";
                        $hq->save();
                    }
                    if ($id%900 ==0 ){
                        $hq=new Hq15min();
                        $hq->id=$id;
                        $hq->close=$item['close'];
                        $hq->open=$item['open'];
                        $hq->amount=$item['amount'];
                        $hq->low=$item['low'];
                        $hq->vol=$item['vol'];
                        $hq->count=$item['count'];
                        $hq->high=$item['high'];
                        $hq->symbol=$symbol."usdt";
                        $hq->save();
                    }
                    if ($id%1800 ==0 ){
                        $hq=new Hq30();
                        $hq->id=$id;
                        $hq->close=$item['close'];
                        $hq->open=$item['open'];
                        $hq->amount=$item['amount'];
                        $hq->low=$item['low'];
                        $hq->vol=$item['vol'];
                        $hq->count=$item['count'];
                        $hq->high=$item['high'];
                        $hq->symbol=$symbol."usdt";
                        $hq->save();
                    }


                }





            }
        }
        return $this->success("采集行情成功");


    }


}