<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Product;


class StockController extends Controller
{
    public  static $apiKey="7601b3442ebb4740bc8e8ea209f0b10b";
    //  http://192.168.16.100/api/v2/forexList
    public function forexList(){
        $limit=100;
        $page=1;
        $list = Product::where('is_forex', 1)
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        return $this->success($list);
    }

    //http://192.168.1.6/api/v2/stockList
    public function stockList(){
        $limit=100;
        $page=1;
        $list = Product::where('is_forex', 2)
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        return $this->success($list);
    }

    //股票K线  http://192.168.16.100/api/v2/kline?symbol=AAPL&interval=1
    public function kline(Request $request){
        $symbol=$request->input("symbol");
        $interval=$request->input("interval","1min");
        $url="https://api.twelvedata.com/time_series?symbol=".$symbol."&interval=".$interval."&apikey=".self::$apiKey;
        $result=file_get_contents($url);
        $result=json_decode($result);
        $values=$result->values;
        foreach ($values as $kline){
            $datetime=$kline->datetime;
            $kline->datetime=strtotime($datetime);
        }

        return $this->success($result);
    }
    protected static $httpClient = null;
    public static function getHttpClient()
    {
        if (!self::$httpClient) {
            self::$httpClient = new Client([

            ]);
        }
        return self::$httpClient;
    }

}