<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{CurrencyMatch, CurrencyQuotation};

class AutoLastClose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_last_close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动采集前一天币种收盘价';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info('开始执行自动采集前一天币种收盘价脚本-' . $now->toDateTimeString());
        
        
        $list = CurrencyMatch::getAlltickMatchs();
        $gp_list =[];
        $other_list = [];
        foreach ($list as $k => $v) {
            if($v->market_from == 3) {
                // 股票
                $gp_list[] = $v;
            }elseif($v->market_from == 4) {
                $other_list[] = $v;
            }
        }
        // 股票
        $gp_post_data = [
        "trace" => "c2a8a146-a647-4d6f-ac07-8c4805bf6854",
         "data" => [
             "data_list" => []
             ]
        ];
        foreach ($gp_list as $k => $v) {
            array_push(
                $gp_post_data['data']['data_list'],
                [
                    "code" => $v['currency_name'],
                    "kline_type" => 1,
                    "kline_timestamp_end" => 0,
                    "query_kline_num" => 1,
                    "adjust_type" => 0
                  ]);
        }
        $gp_url = config('websocket.alltick_client.gp_url')."/batch-kline?token=".config('websocket.alltick_client.token');
        $result = curl_post($gp_url,$gp_post_data,false,true,array('trace: c2a8a146-a647-4d6f-ac07-8c4805bf6854'));
         if($result['ret'] == 200) {
              $this->info('执行成功1');
         }else {
              $this->info('执行成功2');
         }
        
        // 获取币种列表
        // $data = CurrencyMatch::getAlltickMatchs();
        // $this->info('币种总数 '.count($data) . $now->toDateTimeString());
        //  foreach ($data as $k => $v) {
        //      $currency_name = $v->currency_name;
        //      $this->info('币种'.$currency_name.'开始执行'. Carbon::now()->toDateTimeString());
        //      if($v->market_from == 3) {
        //         // 股票
        //         $http_url = config('websocket.alltick_client.gp_url'); // 股票
        //     }elseif($v->market_from == 4) {
        //         // 外汇，贵金属
        //         $http_url = config('websocket.alltick_client.other_url'); // 外汇,加密货币(数字币),商品(贵金属) HTTP接口API地址
        //     }
        //     $token = config('websocket.alltick_client.token');
        //     $url = $http_url.'/kline?token='.$token;
            
        //      $send_data = [
        //          "trace" => "3baaa938-f92c-4a74-a228-fd49d5e2f8bc-1678419655879",
        //          "data" => [
        //             "code" => coinResetName($currency_name),
        //             "kline_type" => 8,
        //             "kline_timestamp_end" => 0,
        //             "query_kline_num"=> 1,
        //             "adjust_type"=> 0
        //              ]
        //     ];
        //     $query = urlencode(json_encode($send_data));
        //     $url = $url.'&query='.$query;
           
        //   try {
        //         $result = file_get_contents($url);
        //         $result = json_decode($result, true);
        //          if($result['ret'] == 200) {
        //             $list = $result['data']['kline_list'];
        //             if($list) {
        //                 $v->last_close = $list[0]['close_price'];
        //                 $res = $v->save();
        //                 if($res){
        //                      $this->info('币种'.$currency_name.'采集更新成功!' . $now->toDateTimeString());
        //                 }else {
        //                     $this->info('币种'.$currency_name.'采集更新失败!' . $now->toDateTimeString());
        //                 }
        //             }else {
        //                  $this->info('币种'.$currency_name.'采集失败!' . $now->toDateTimeString());
        //             }
        //          }else {
        //              $this->info('币种'.$currency_name.'采集失败!' . $now->toDateTimeString());
        //         }
        //       } catch (\Exception $e) {
        //           $this->info('接口请求失败!' . $url);
        //   }
           
        //     //   每5秒执行一次
        //     sleep(10);
        //  }
          $this->info('执行成功');
    }
}
