<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\{Currency, CurrencyPlate, CurrencyMatch, CurrencyQuotation,Hq1sec,Hq1min,Hq5min,Hq15min,Hq30min,Hq60min,Hq1day,Hq1week,Hq1mon,HuobiSymbol,HqPrice};
use App\Jobs\{LeverUpdate, SendMarket};
use Workerman\Worker;
use App;

use Workerman\Connection\AsyncTcpConnection;

class HbController extends Controller
{
    //   https://	hi2023.hayasthw.xyz/hb_index
    public function index(){
        $this->subscribe(function($data) {
            var_dump($data);
        });
    }
    public function subscribe($callback, $sub_str="market.btcusdt.kline.1min") {
        $GLOBALS['sub_str'] = $sub_str;
        $GLOBALS['callback'] = $callback;
        $worker = new Worker();
        $worker->onWorkerStart = function($worker) {
            // ssl需要访问443端口
            $con = new AsyncTcpConnection('ws://api.huobi.pro:443/ws');

            // 设置以ssl加密方式访问，使之成为wss
            $con->transport = 'ssl';

            $con->onConnect = function($con) {
                $data = json_encode([
                    'sub' => $GLOBALS['sub_str'],
                    'id' => 'depth' . time()
                ]);
                $con->send($data);
            };

            $con->onMessage = function($con, $data) {
                $data = gzdecode($data);
                $data = json_decode($data, true);
                if(isset($data['ping'])) {
                    $con->send(json_encode([
                        "pong" => $data['ping']
                    ]));
                }else{
                    call_user_func_array($GLOBALS['callback'], array($data));
                }
            };

            $con->connect();
        };

        Worker::runAll();
    }
    
    // 获取火币列表
    public function getHbList(Request $request) {
        // $data = CurrencyMatch::getAlltickMatchs();
        // return $this->success($data);
        $type = $request->get('type',-1);
        $data = CurrencyQuotation::getCurrencyQuotationList($type);
        return $this->success($data);
    }
    
    public function calcIncreasePair($kline_data)
    {
        $open = $kline_data['open_price'];
        $close = $kline_data['close_price'];;
        $change_value = bc_sub($close, $open);
        $change = bc_mul(bc_div($change_value, $open), 100, 2);
        return $change;
    }
    
    // 获取单个火币最新详情
    public function getHbDetail(Request $request) {
        $currency_name = $request->get('coin','BTC');
        $currency_name = strtoupper($currency_name);
        $data = CurrencyQuotation::getCurrencyQuotationDetail($currency_name);
         if($data) {
             return $this->success($data);
         }else {
             return $this->error("该火币不存在！");
         }
        
    }
    
     // 获取币种浮动价格列表
    public function getHbFloatPriceList(Request $request) {
        $data = HqPrice::orderBy('id', 'desc')->limit(1)->get();
        return $this->success($data);
    }
    
    // 获取火币历史k线图
    public function getHbHistoryKline(Request $request) {
        $currency_name = $request->get('coin','BTC-USDT');
        $period = $request->get('period','1min');
        $size = $request->get('size',500);
        $currency_name = strtolower($currency_name);
        $symbol = str_replace('-','',$currency_name);
        $symbol = strtolower($symbol);
        try {
            // 查询币种信息
            $coin_name = explode('-',$currency_name);
            $currency = Currency::where('name',$coin_name[0])->first();
            $historyKline = [];
            if($currency) {
                switch($currency->coin_type) {
                    case 0:
                        // 火币
                        $url = 'https://api.huobi.pro/market/history/kline?symbol='.$symbol.'&period='.$period.'&size='.$size;
                        $result = file_get_contents($url);
                        $result_data = json_decode($result, true);
                         if($result_data['status'] == 'ok') {
                            $historyKline = $result_data['data'];
                         }
                    break;
                    case 1:
                    case 2:
                    case 3:
                        // alltick币
                        // k线类型，1分钟K，2为5分钟K，3为15分钟K，4为30分钟K，5为小时K，6为2小时K，7为4小时K，8为日K，9为周K，10为月K （注：股票不支持2小时K、4小时K）
                        $kline_type = 1;
                        switch($period) {
                            case '1min':
                                $kline_type = 1;
                            break;
                             case '15min':
                                $kline_type = 3;
                            break;
                             case '30min':
                                $kline_type = 4;
                            break;
                             case '60min':
                                $kline_type = 5;
                            break;
                             case '1day':
                                $kline_type = 8;
                            break;
                             case '1week':
                                $kline_type = 9;
                            break;
                             case '1mon':
                                $kline_type = 10;
                            break;
                        }
                        if($currency->coin_type == 1) {
                            // 股票
                            $http_url = config('websocket.alltick_client.gp_url'); // 股票
                        }else {
                            // 外汇，贵金属
                            $http_url = config('websocket.alltick_client.other_url'); // 外汇,加密货币(数字币),商品(贵金属) HTTP接口API地址
                        }
                        $token = config('websocket.alltick_client.token');
                        $url = $http_url.'/kline?token='.$token;
                        
                         $send_data = [
                             "trace" => "3baaa938-f92c-4a74-a228-fd49d5e2f8bc-1678419655879",
                             "data" => [
                                 "code" => coinResetName($currency->name),
                                "kline_type" => $kline_type,
                                "kline_timestamp_end" => 0,
                                "query_kline_num"=> $size,
                                "adjust_type"=> 0
                                 ]
                        ];
                        $query = urlencode(json_encode($send_data));
                        $url = $url.'&query='.$query;
                       
                        $result = file_get_contents($url);
                        $result = json_decode($result, true);
                        
                        if($result['ret'] == 200) {
                            $list = $result['data']['kline_list'];
                            $list_data = [];
                            foreach ($list as $k => $v) {
                                $item = [
                                    'id' => $v['timestamp'],
                                    'high' => $v['high_price'],
                                    'low' => $v['low_price'],
                                    'open' => $v['open_price'],
                                    'close' => $v['close_price'],
                                    'vol' => $v['volume'],
                                    'trade_turnover' => $v['turnover']
                                ];
                                $list_data[] = $item;
                            }
                            $historyKline = $list_data;
                        }
                    break;
                }
            }
            // 查询插针数据并替换
           $hbData = [];
           $symbol = str_replace('-','',$currency_name);
           $symbol = strtolower($symbol);
           switch ($period) {
               case '1min':
                   $hbData = Hq1min::where("symbol",$symbol)->orderBy('id', 'desc')->get();
                   break;
               case '5min':
                   $hbData = Hq5min::where("symbol",$symbol)->orderBy('id', 'desc')->get();
                   break;
               case '15min':
                   $hbData = Hq15min::where("symbol",$symbol)->orderBy('id', 'desc')->get();
                   break;
               case '30min':
                   $hbData = Hq30min::where("symbol",$symbol)->orderBy('id', 'desc')->get();
                   break;
               case '60min':
                   $hbData = Hq60min::where("symbol",$symbol)->orderBy('id', 'desc')->get();
                   break;
               case '1day':
                   $hbData = Hq1day::where("symbol",$symbol)->orderBy('id', 'desc')->get();
                   break;
               case '1week':
                   $hbData = Hq1week::where("symbol",$symbol)->orderBy('id', 'desc')->get();
                   break;
               case '1mon':
                   $hbData = Hq1mon::where("symbol",$symbol)->orderBy('id', 'desc')->get();
                   break;
           }
           // 查询浮动价格列表
           $priceData = HqPrice::where("symbol",$symbol)->orderBy('id', 'desc')->get();
           $last_close = 0; // 上一分钟的收盘价
           foreach ($historyKline as $k => $v) {
               $change = bc_mul(bc_div(bc_sub($v['close'],$v['open']),$v['open']),100,2);
               $historyKline[$k]['change'] = $change;
               // 查询这一分钟是否存在插针
               $is_hq = Hq1min::where("symbol",$symbol)->where("id",$v['id'])->first();
               if($is_hq) {
                   foreach ($hbData as $hk => $hv) {
                       if($v['id'] == $hv['id']) {
                            $historyKline[$k]['close'] = $hv['close'];
                            $historyKline[$k]['open'] = $hv['open'];
                            $historyKline[$k]['high'] = $hv['high'];
                            $historyKline[$k]['low'] = $hv['low'];
                            $historyKline[$k]['vol'] = $hv['vol'];
                            $change = bc_mul(bc_div(bc_sub($hv['close'],$hv['open']),$hv['open']),100,2);
                            $historyKline[$k]['change'] = $change;
                        }
                  }
               }else {
                   // 浮动价格替换
                   if($priceData) {
                       foreach ($priceData as $pk => $pv) {
                           // 是否替换数据
                           $is_replace = false;
                           if($pv['end_time'] == 0 && $v['id'] >= $pv['start_time']) { // 没有终止时间，则可以直接替换
                               $is_replace = true;
                           }else
                            if($pv['start_time'] <= $v['id'] && $v['id'] < $pv['end_time']) {
                                $is_replace = true;
                            }
                            // 替换数据
                            if($is_replace) {
                                $open = bcadd($v['open'],$pv['float_price']);
                                $close = bcadd($v['close'],$pv['float_price']);
                                $high = bcadd($v['high'],$pv['float_price']);
                                $low = bcadd($v['low'],$pv['float_price']);
                                
                                $historyKline[$k]['original_close'] = $v['close'];
                                
                                $historyKline[$k]['close'] = $close;
                                $historyKline[$k]['open'] = $open;
                                $historyKline[$k]['high'] = $high;
                                $historyKline[$k]['low'] = $low;
                                $change = bc_mul(bc_div(bc_sub($close,$open),$open),100,2);
                                $historyKline[$k]['change'] = $change;
                            }
                       }
                   }
               }
               // 防止中途断开，每一次的开盘价都等于上一次的收盘价
               if($last_close > 0) {
                   $historyKline[$k]['open'] = $last_close;
               }
              $last_close = $historyKline[$k]['close'];
           }
           return $this->success($historyKline);
        } catch (\Exception $e ) {
            return $this->error($e);
        }
    }
    
    // 获取火币获取行情深度数据
    public function getHbDepth(Request $request) {
        $currency_name = $request->get('coin','BTC-USDT');
        $type = $request->get('type','step6');
        $currency_name = strtoupper($currency_name);
        $symbol = str_replace('-','',$currency_name);
        $symbol = strtolower($symbol);
        try {
             // 查询币种信息
            $coin_name = explode('-',$currency_name);
            $currency = Currency::where('name',$coin_name[0])->first();
            $listData = [];
            switch($currency->coin_type) {
                case 0:
                // 火币
                $url = 'https://api.huobi.pro/market/depth?symbol='.$symbol.'&type='.$type;
                 $result = file_get_contents($url);
                 $array = json_decode($result, true);
                 if($array['status'] == 'ok') {
                    $listData = $array['tick'];
                 }
                break;
                case 1:
                case 2:
                case 3:
                    if($currency->coin_type == 1) {
                        // 股票
                        $http_url = config('websocket.alltick_client.gp_url'); // 股票
                    }else {
                        // 外汇，贵金属
                        $http_url = config('websocket.alltick_client.other_url'); // 外汇,加密货币(数字币),商品(贵金属) HTTP接口API地址
                    }
                    $token = config('websocket.alltick_client.token');
                    $url = $http_url.'/depth-tick?token='.$token;
                    $send_data = [
                         "trace" => "3baaa938-f92c-4a74-a228-fd49d5e2f8bc-1678419653657",
                         "data" => [
                             "symbol_list" => [
                                    "code" => coinResetName($currency->name)
                                 ]
                             ]
                    ];
                    $query = urlencode(json_encode($send_data));
                    $url = $url.'&query='.$query;
                   
                    $result = file_get_contents($url);
                    $result = json_decode($result, true);
                    if($result['ret'] == 200) {
                        $list = $result['data']['tick_list'];
                        $bids = [];
                        $asks = [];
                        foreach ($list as $k => $v) {
                            foreach ($v['bids'] as $bk => $bv) {
                                $bids[] = [(float)$bv['price'],(float)$bv['volume']];
                            }
                            foreach ($v['asks'] as $ak => $av) {
                                $asks[] = [(float)$av['price'],(float)$av['volume']];
                            }
                        }
                        
                        $listData = [
                            'mrid' => $result['data']['tick_list'][0]['seq'],
                            'ts' => (int)$result['data']['tick_list'][0]['tick_time'],
                            'bids' => $bids,
                            'asks' => $asks
                        ];
                    }
                break;
            }
             return $this->success($listData);
        } catch (\Exception $e ) {
            return $this->error($e);
        }
    }
    
     // 获取火币批量获取市场最近成交记录
    public function getHbHistoryTrade(Request $request) {
        $currency_name = $request->get('coin','BTC-USDT');
        $size = $request->get('size',500);
        $currency_name = strtoupper($currency_name);
        $symbol = str_replace('-','',$currency_name);
        $symbol = strtolower($symbol);
        try {
             // 查询币种信息
            $coin_name = explode('-',$currency_name);
            $currency = Currency::where('name',$coin_name[0])->first();
            $listData = [];
            switch($currency->coin_type) {
                case 0:
                // 火币
                $url = 'https://api.huobi.pro/market/history/trade?symbol='.$symbol.'&size='.$size;
                 $result = file_get_contents($url);
                 $array = json_decode($result, true);
                 if($array['status'] == 'ok') {
                    $listData = $array['data'];
                 }
                break;
                case 1:
                case 2:
                case 3:
                    if($currency->coin_type == 1) {
                        // 股票
                        $http_url = config('websocket.alltick_client.gp_url'); // 股票
                    }else {
                        // 外汇，贵金属
                        $http_url = config('websocket.alltick_client.other_url'); // 外汇,加密货币(数字币),商品(贵金属) HTTP接口API地址
                    }
                    $token = config('websocket.alltick_client.token');
                    $url = $http_url.'/trade-tick?token='.$token;
                    $send_data = [
                         "trace" => "3baaa938-f92c-4a74-a228-fd49d5e2f8bc-1678419656685",
                         "data" => [
                             "symbol_list" => [
                                    "code" => coinResetName($currency->name)
                                 ]
                             ]
                    ];
                    $query = urlencode(json_encode($send_data));
                    $url = $url.'&query='.$query;
                   
                    $result = file_get_contents($url);
                    
                    $result = json_decode($result, true);
                    if($result['ret'] == 200) {
                        $list = $result['data']['tick_list'];
                        $list_data = [];
                        foreach ($list as $k => $v) {
                            $item = [
                                'id' => $v['seq'],
                                'ts' => (int)$v['tick_time'],
                                'data' => [
                                    [
                                    'amount' => $v['turnover'],
                                    'quantity' => $v['volume'],
                                    'trade_turnover' => $v['trade_direction'],
                                    'price' => $v['price'],
                                    'direction' => $v['trade_direction'] == 1 ? 'buy' : 'sell', // 交易方向，0为默认值，1为BUY，2为SELL
                                    'id' => $v['seq'],
                                    'ts' => (int)$v['tick_time'],
                                    'trade_turnover' => $v['turnover']
                                    ]
                                ]
                            ];
                            $list_data[] = $item;
                        }
                        $listData = $list_data;
                    }
                break;
            }
             return $this->success($listData);
        } catch (\Exception $e ) {
            return $this->error($e);
        }
    }

}