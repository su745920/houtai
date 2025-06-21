<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use App\Models\Hq30;
use App\Models\InsuranceType;
use App\Models\UsersInsurance;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Hq30Controller extends Controller
{

    //   https://mywordpro.xyz/api/updateHq

    public function updateHq(){
       $t=1689861600;
       $date=date("Y-m-d H:i:s",$t);
       echo $date;
    }

    //   https://mywordpro.xyz/api/saveHq
    public function findKline(){

    }


    public function  saveHq()
    {

        $url="https://api.huobi.pro/market/history/kline?period=30min&size=200&symbol=btcusdt";
        $res = file_get_contents($url);

        $res = json_decode($res, true);
        $data=$res['data'];

        foreach ($data as $item){
            echo $item['id']."  ".$item['close'];
            $id=$item['id'];
            $tmp=Hq30::getById($id);
            if (empty($tmp)){
                $hq=new Hq30();
                $hq->id=$id;
                $hq->close=$item['close'];
                $hq->open=$item['open'];
                $hq->amount=$item['amount'];

                $hq->low=$item['low'];
                $hq->vol=$item['vol'];
                $hq->count=$item['count'];
                $hq->high=$item['high'];

                $hq->save();


            }
        }

    }


}