<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\{AccountLog, Currency, Hq1sec,Hq1min,Hq5min, Hq15min, Hq30min,Hq60min, Hq1day,Hq1week,Hq1mon , Users, UsersWallet, RechargeRecord,HqPrice,UserChat};
use Illuminate\Support\Facades\DB;
use Faker\Factory;
use App\Jobs\{SendMarket};
use App\Logic\SocketLogic;
date_default_timezone_set('Asia/Shanghai'); // 设置为上海时区

class AdminHqController extends Controller
{
    /**
     *
     *https://mywordpro.xyz/admin/hqControl/saveAjax?currency_id=BTC&minPrice=31000&maxPrice=32000&fromYear=2023&fromMonth=7&fromDay=21&fromH=5&fromMinute=3&toYear=2023&toMonth=7&toDay=21&toH=5&toMinute=22
    currency_id: BTC





    toMonth: 07
    toDay: 21
    toH: 5
    toMinute: 24
     */
      
    public function saveAjax(Request $request){
        $symbol= $request->post("symbol");


        $symbol=strtolower($symbol);
        
        $k1min = $request->post('kline');
        $date = $request->post('date');
        $k5min = $request->post('k5min');
        $k15min = $request->post('k15min');
        $k30min = $request->post('k30min');
        $k60min = $request->post('k60min');
        $k1day = $request->post('k1day');
        $k1week = $request->post('k1week');
        $k1mon = $request->post('k1mon');
        try {
            DB::beginTransaction();
            // 一分钟
            $k1minData = json_decode($k1min, true);
            for ($i = 0; $i < count($k1minData); $i++) {
                $hq1=new Hq1min();
                $hq1->id = strtotime($k1minData[$i][0]);
                $hq1->open = $k1minData[$i][1];
                $hq1->high = $k1minData[$i][2];
                $hq1->low = $k1minData[$i][3];
                $hq1->close = $k1minData[$i][4];
                $hq1->vol = $k1minData[$i][5];
                $hq1->symbol=$symbol."usdt";
                $hq1->control=1;
                $hq1->save();
            }
            // 5分钟
            $k5minData = json_decode($k5min, true);
            for ($i = 0; $i < count($k5minData); $i++) {
                $hq1=new Hq5min();
                $hq1->id = strtotime($k5minData[$i][0]);
                $hq1->open = $k5minData[$i][1];
                $hq1->high = $k5minData[$i][2];
                $hq1->low = $k5minData[$i][3];
                $hq1->close = $k5minData[$i][4];
                $hq1->vol = $k5minData[$i][5];
                $hq1->symbol=$symbol."usdt";
                $hq1->control=1;
                $hq1->save();
            }
            // 15分钟
            $k15minData = json_decode($k15min, true);
            for ($i = 0; $i < count($k15minData); $i++) {
                $hq1=new Hq15min();
                $hq1->id = strtotime($k15minData[$i][0]);
                $hq1->open = $k15minData[$i][1];
                $hq1->high = $k15minData[$i][2];
                $hq1->low = $k15minData[$i][3];
                $hq1->close = $k15minData[$i][4];
                $hq1->vol = $k15minData[$i][5];
                $hq1->symbol=$symbol."usdt";
                $hq1->control=1;
                $hq1->save();
            }
            // 30分钟
            $k30minData = json_decode($k30min, true);
            for ($i = 0; $i < count($k30minData); $i++) {
                $hq1=new Hq30min();
                $hq1->id = strtotime($k30minData[$i][0]);
                $hq1->open = $k30minData[$i][1];
                $hq1->high = $k30minData[$i][2];
                $hq1->low = $k30minData[$i][3];
                $hq1->close = $k30minData[$i][4];
                $hq1->vol = $k30minData[$i][5];
                $hq1->symbol=$symbol."usdt";
                $hq1->control=1;
                $hq1->save();
            }
            // 60分钟
            $k60minData = json_decode($k60min, true);
            for ($i = 0; $i < count($k60minData); $i++) {
                $hq1=new Hq60min();
                $hq1->id = strtotime($k60minData[$i][0]);
                $hq1->open = $k60minData[$i][1];
                $hq1->high = $k60minData[$i][2];
                $hq1->low = $k60minData[$i][3];
                $hq1->close = $k60minData[$i][4];
                $hq1->vol = $k60minData[$i][5];
                $hq1->symbol=$symbol."usdt";
                $hq1->control=1;
                $hq1->save();
            }
            // 1天
            $k1dayData = json_decode($k1day, true);
            for ($i = 0; $i < count($k1dayData); $i++) {
                $hq1=new Hq1day();
                $hq1->id = strtotime($k1dayData[$i][0]);
                $hq1->open = $k1dayData[$i][1];
                $hq1->high = $k1dayData[$i][2];
                $hq1->low = $k1dayData[$i][3];
                $hq1->close = $k1dayData[$i][4];
                $hq1->vol = $k1dayData[$i][5];
                $hq1->symbol=$symbol."usdt";
                $hq1->control=1;
                $hq1->save();
            }
            // 1周
            $k1weekData = json_decode($k1week, true);
            for ($i = 0; $i < count($k1weekData); $i++) {
                $hq1=new Hq1week();
                $hq1->id = strtotime($k1weekData[$i][0]);
                $hq1->open = $k1weekData[$i][1];
                $hq1->high = $k1weekData[$i][2];
                $hq1->low = $k1weekData[$i][3];
                $hq1->close = $k1weekData[$i][4];
                $hq1->vol = $k1weekData[$i][5];
                $hq1->symbol=$symbol."usdt";
                $hq1->control=1;
                $hq1->save();
            }
            // 1月
            $k1monData = json_decode($k1mon, true);
            for ($i = 0; $i < count($k1monData); $i++) {
                $hq1=new Hq1mon();
                $hq1->id = strtotime($k1monData[$i][0]);
                $hq1->open = $k1monData[$i][1];
                $hq1->high = $k1monData[$i][2];
                $hq1->low = $k1monData[$i][3];
                $hq1->close = $k1monData[$i][4];
                $hq1->vol = $k1monData[$i][5];
                $hq1->symbol=$symbol."usdt";
                $hq1->control=1;
                $hq1->save();
            }
            DB::commit();
            return $this->success("插针成功");
        } catch (\Exception $e ) {
             DB::rollBack();
            return $this->error($th->getMessage());
        }
        
        
    }
     public function previewKLine(Request $request) {
        $numbers = $request->get('insert_range');
        $rate1 = $request->get('rate1');
        $rate2 = $request->get('rate2');
        $speed0 = $request->get('speed0');
        $speed1 = $request->get('speed1');
        $jingdu = $request->get('jingdu');
        $now = [];
        $vol0 = $request->get('vol0');
        $vol1 = $request->get('vol1');
        $start = $request->get('start');
        
        $hq1minData = $this->getPreviewKLine(1,$start,$numbers,$rate1,$rate2,$speed0,$speed1,$jingdu,$vol0,$vol1);
        $hq5minData = [];
        $hq15minData = [];
        $hq30minData = [];
        $hq60minData = [];
        $hq1dayData = [];
        $hq1weekData = [];
        $hq1monData = [];
        if($numbers%5 == 0) {
             $hq5minData = $this->getPreviewKLine(5,$start,$numbers,$rate1,$rate2,$speed0,$speed1,$jingdu,$vol0,$vol1);
        }
        if($numbers%15 == 0) {
             $hq15minData = $this->getPreviewKLine(15,$start,$numbers,$rate1,$rate2,$speed0,$speed1,$jingdu,$vol0,$vol1);
        }
        if($numbers%30 == 0) {
             $hq30minData = $this->getPreviewKLine(30,$start,$numbers,$rate1,$rate2,$speed0,$speed1,$jingdu,$vol0,$vol1);
        }
        if($numbers%60 == 0) {
             $hq60minData = $this->getPreviewKLine(60,$start,$numbers,$rate1,$rate2,$speed0,$speed1,$jingdu,$vol0,$vol1);
        }
        if($numbers%1440 == 0) {
             $hq1dayData = $this->getPreviewKLine(1440,$start,$numbers,$rate1,$rate2,$speed0,$speed1,$jingdu,$vol0,$vol1);
        }
        if($numbers%10080 == 0) {
             $hq1weekData = $this->getPreviewKLine(10080,$start,$numbers,$rate1,$rate2,$speed0,$speed1,$jingdu,$vol0,$vol1);
        }
        if($numbers%43200 == 0) {
             $hq1monData = $this->getPreviewKLine(43200,$start,$numbers,$rate1,$rate2,$speed0,$speed1,$jingdu,$vol0,$vol1);
        }
        // 展示分钟
        return [
            'data' => $hq1minData, 
            'k5min' => $hq5minData,
            'k15min' => $hq15minData,
            'k30min' => $hq30minData,
            'k60min' => $hq60minData,
            'k1day' => $hq1dayData,
            'k1week' => $hq1weekData,
            'k1mon' => $hq1monData,
            'next' => date('Y-m-d H:i', strtotime('+' . $numbers . ' minutes', strtotime($_GET['dates'] . ':00')))];
    }
    
      public function getPreviewKLine($multiple,$start,$numbers,$rate1,$rate2,$speed0,$speed1,$jingdu,$vol0,$vol1) {
        $numbers = $numbers/$multiple;
        $speed0 = $speed0*$multiple;
        $speed1 = $speed1*$multiple;
        $now = [];
        $faker = Factory::create();
        

        for ($i = 0; $i < $numbers; $i++) {
            if ($i === 0) {
                $now[] = $start;
            } else {
                $rand = $faker->randomFloat($jingdu, $speed0, $speed1);
                if ($this->get_rand([$rate2, $rate1]) === 1) {
                    $now[] = $now[$i - 1] + $rand;
                } else {
                    $now[] = $now[$i - 1] - $rand;
                }
            }
        }
        $obj = [];
        foreach ($now as $number) {
            $arr = ['open' => count($obj) === 0 ? $start : $obj[count($obj) - 1]['close']];

            // $arr['close'] = $number + $faker->randomFloat($jingdu, $speed0, $speed1);
            $arr['close'] = $number;
            if (count($now) > 1 && count($obj) === 0) {
                $arr['close'] = $number + $faker->randomFloat($jingdu, $speed0, $speed1);
            }else {
                // 针对一分钟特殊处理
                if($this->get_rand([$rate2, $rate1]) === 1) {
                    // 涨
                    // $arr['close'] = $number + $faker->randomFloat($jingdu, $speed0, $speed1);
                    $arr['close'] = $number;
                }else {
                    // 跌
                    // $arr['close'] = $number - $faker->randomFloat($jingdu, $speed0, $speed1);
                    $arr['close'] = $number;
                }
            }


            $val = max(array_values($arr));
            $minVal = min(array_values($arr));

            $arr['high'] = $val + $faker->randomFloat($jingdu, $speed0, $speed1) * $faker->randomFloat(2, 0.5, 0.50);
            $arr['low'] = $minVal - $faker->randomFloat($jingdu, $speed0, $speed1) * $faker->randomFloat(2, 0.05, 0.50);

            array_walk($arr, function (&$val) use ($jingdu) {
                $val = sprintf('%.' . $jingdu . 'f', $val);
            });
            $obj[] = $arr;

        }

        $rsp = [];
        $i = 0;
        foreach ($obj as $v) {
            $rsp[] = [date('Y-m-d H:i:00', strtotime("+{$i} minutes", strtotime($_GET['dates']))), $v['open'], $v['high'], $v['low'], $v['close'], rand($vol0, $vol1)];

            $i++;
        }
        return $rsp;
    }
    
     private function get_rand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    
    public function getKlineData($fromTime,$toTime,$minPrice,$maxPrice,$seconds = 60) {
        $klineData = [];
        $decimal = 100000;
        // $seconds = 60;

        $open_price = $maxPrice;
        $close_price = $minPrice;
        $high_price = $maxPrice;
        $low_price = $minPrice;
        $min_amount = $minPrice;
        $max_amount = $maxPrice;
        $start = $fromTime;
        $end = $toTime;

        $period_seconds = 1800;
        $periodCount = 86400 / $period_seconds;
        $unit = number_format(bcdiv(($high_price - $low_price), $periodCount - 1, 8), 8, '.', '');

        // 24小时 周期价格上涨下跌趋势随机 1涨2跌
        $period2_seconds = 1800;
        $periodCount2 = 86400 / $period2_seconds;
        $periodsTrend = [];
        for ($i = 0; $i < $periodCount2; $i++) {
            if ($close_price > $open_price) {
                $thresholdValue = 60;
            } else {
                $thresholdValue = 40;
            }
            $periodsTrend[$start + ($i * $period2_seconds)] = mt_rand(1, 100) <= $thresholdValue ? 1 : 2;
        }

        $ups_downs_high     = abs(floor($unit * $decimal * 8));            //高
        $ups_downs_value    = abs(floor($unit * 3 * $decimal));            //值
        $ups_downs_low      = abs($unit);              //低

        $current = $start;
        $kkk = 1;
        
        while ($current < $end) {
            //            echo $current . '--' . $kkk . "\r\n";
            // 获取上一条
            $prev_time = $current - $seconds;
            $prev = Arr::first($klineData ?? [], function ($v, $k) use ($prev_time) {
                return $v['timestamp'] == $prev_time;
            });
            if (blank($prev)) {
                $price = $open_price * $decimal;
            } else {
                $price = $prev['close'] * $decimal;
            }
            $amount = mt_rand($min_amount * $decimal, $max_amount * $decimal) / $decimal;
            $volume = bcdiv($amount, $price);
            $open = $price;
            $up_or_down = mt_rand(1, 100);

            $flag = Arr::first($periodsTrend, function ($v, $k) use ($current, $period2_seconds) {
                return $current >= $k && $current < ($k + $period2_seconds);
            });
            // var_dump($current . '--' . $flag);
            if ($flag == 1) {
                $value = 80;
            } else {
                $value = 20;
            }

            // dump($ups_downs_low,$ups_downs_value,$ups_downs_high);
            if ($up_or_down <= $value) {
                // 涨
                $close = mt_rand($price, $price + mt_rand($ups_downs_low, $ups_downs_high));
                $high = mt_rand($close, $close + mt_rand($ups_downs_low, $ups_downs_value));
                $low = mt_rand($close - mt_rand($ups_downs_low, $ups_downs_value), $close);
            } else {
                // 跌
                $close = mt_rand($price - mt_rand($ups_downs_low, $ups_downs_high), $price);
                $high = mt_rand($close, $close + mt_rand($ups_downs_value, $ups_downs_high));
                $low = mt_rand($close - mt_rand($ups_downs_low, $ups_downs_value), $close);
            }

            if ($current == $start) {
                $open = $open_price * $decimal;
            } elseif ($current + $seconds == $end) {
                $close = $close_price * $decimal;
            }

            $high = max($open, $close, $high, $low);
            $low = min($open, $close, $high, $low);

            $open = $open / $decimal;
            $close = $close / $decimal;
            $high = $high / $decimal;
            $low = $low / $decimal;
            $klineItem = [
                'timestamp' => $current,
                'datetime' => date('Y-m-d H:i:s', $current),
                'open' => $open,
                'close' => $close,
                'high' => $high,
                'low' => $low,
                'volume' => $volume,
                'amount' => $amount,
                'period' => 1,
            ];
            array_push($klineData, $klineItem);

            $current += $seconds;
            $kkk++;
        }
        return $klineData;
    }
    
    public function index(){
        
        
        $currencies =  Currency::where('is_display', '1', 1)->where('name','!=','USDT')->orderBy('sort','desc')->get();

        $hList=[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23];

        $minuteList=[];
        for ($i=0; $i<=59; $i++)
        {
            array_push($minuteList,$i);
        }
        $day = date('d');
        return view('admin.hq_control.index')->with('currencies', $currencies)->with('hList', $hList)->with('minuteList', $minuteList)->with('day',$day);
    }
    public function priceIndex(Request $request) {
        $currencies =  Currency::where('is_display', '1', 1)->where('name','!=','USDT')->orderBy('sort','desc')->get();
        $prices = HqPrice::orderBy('id', 'desc')->limit(1)->get();
        foreach ($currencies as $k => $v) {
            foreach ($prices as $pk => $pv) {
                if($v->id == $pv->currency_id) {
                    $currencies[$k]['float_price'] = $pv->float_price;
                    continue;
                }
            }
        }
        return view('admin.hq_control.price')->with('currencies', $currencies);
    }
    // 保存浮动价格
     public function savePrice(Request $request){
        $currency_id = $request->post("currency_id");
        $float_price = $request->post("float_price",0);
        try {
            DB::beginTransaction();
            // 查询是否存在
            $is_price = HqPrice::where('currency_id',$currency_id)->latest()->first();
            $currency = Currency::find($currency_id);
            $symbol = strtolower($currency->name)."usdt";
            // 判断是否设置了插针
        //     $hbData = [];
        //     $hbData = Hq1min::where("symbol",$symbol)->get();
        //     if(count($hbData) == 0) {
        //          $hbData = Hq5min::where("symbol",$symbol)->get();
        //     }
        //     if(count($hbData) == 0) {
        //          $hbData = Hq15min::where("symbol",$symbol)->get();
        //     }
        //     if(count($hbData) == 0) {
        //          $hbData = Hq30min::where("symbol",$symbol)->get();
        //     }
        //     if(count($hbData) == 0) {
        //          $hbData = Hq60min::where("symbol",$symbol)->get();
        //     }
        //     if(count($hbData) == 0) {
        //          $hbData = Hq1day::where("symbol",$symbol)->get();
        //     }
        //     if(count($hbData) == 0) {
        //          $hbData = Hq1week::where("symbol",$symbol)->get();
        //     }
        //     if(count($hbData) == 0) {
        //          $hbData = Hq1mon::where("symbol",$symbol)->get();
        //     }
        //   if(count($hbData) > 0) {
        //       return $this->error('已设置插针数据,不可同时设置浮动价格');
        //   }
            if($is_price) {
                // 更新 结束上一段数据
                $is_price->end_time = strtotime(date('Y-m-d H:i:00'));
                $is_price->save();
                // 同时插入新的数据
                $hq_price = new HqPrice();
                $hq_price->currency_id = $currency->id;
                $hq_price->float_price = $float_price;
                $hq_price->symbol = $symbol;
                $hq_price->start_time = strtotime(date('Y-m-d H:i:00'));
                $hq_price->save();
            }else {
                // 新增
                $hq_price = new HqPrice();
                $hq_price->currency_id = $currency->id;
                $hq_price->float_price = $float_price;
                $hq_price->symbol = $symbol;
                $hq_price->start_time = strtotime(date('Y-m-d H:i:00'));
                $hq_price->save();
            }
            DB::commit();
            return $this->success("保存成功！");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        
     }

}