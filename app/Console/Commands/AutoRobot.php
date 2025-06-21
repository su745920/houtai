<?php

namespace App\Console\Commands;

use App\Models\AutoRobot as AutoRobots;
use App\Models\CurrencyQuotation;
use App\Models\Currency;
use App\Models\MarketHour;
use App\Models\Setting;
use App\Models\TransactionComplete;
use App\Models\Transaction;
use App\Models\TransactionIn;
use App\Models\TransactionOut;
use App\Models\UsersWallet;
use App\Models\UserChat;
use Carbon\Carbon;
use Faker\Factory;

use App\Jobs\LeverPushPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Jobs\{EsearchMarket, LeverUpdate, SendMarket,HandleMicroTrade,CoinTradeHandel};

class AutoRobot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_robot {id : id}';// id 为数据库的字端id

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动下单机器人';

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
	 * 机器人自动下单
	 */
	public function handle()
    {
        $id = $this->argument('id');
        

        $faker = Factory::create();
        DB::beginTransaction();
        try{
            while (!empty($auto = AutoRobots::find($id))){

                if (empty($auto->is_start)){
                    DB::rollback();
                    return $this->error('机器人已关闭-'.Carbon::now()->toDateTimeString());

                }else{

                    $this->info('开启机器人-'.Carbon::now()->toDateTimeString());
                    $price_precision = $auto['price_precision'];
                    $num_precision = $auto['num_precision'];
                    $rand_price = $faker->randomFloat($price_precision,$auto['up_price'],$auto['down_price']);
                    //获取到当前区间
                    $auto_data = AutoRobots::getPriceArea($auto,$rand_price);
                    
                    $up_or_down = $auto_data["up_or_down"];
                    $price_area = $auto_data["new_price"];
                    if (!empty($price_area)){
                        $this->info('当前价格区间为 '.$price_area.' 方向为'.$up_or_down);
                        $this->info('设置价格区间为 '.$auto->min_price.'-'.$auto->max_price);
                        $this->info('使用市场价格：'.$auto_data["use_market"].',价格：'.$auto_data["init_price"]);

                        //在价格区间生成记录
                        
                        $number = $faker->randomFloat($num_precision,$auto->min_number,$auto->max_number);
                        
                        $new_complete = new TransactionComplete();
                        $new_complete->user_id = $auto->buy_user_id;
                        $new_complete->from_user_id = $auto->sell_user_id;
                        $new_complete->price = $price_area;
                        $new_complete->number = $number;
                        $new_complete->create_time = time();
                        $new_complete->currency = $auto->currency_id;
                        $new_complete->legal = $auto->legal_id;
                        $result=$new_complete->save();
                        
                        $in_info = TransactionIn::where("currency",$auto->currency_id)->where("user_id",$auto->buy_user_id)->where("price",$price_area)->where("legal",$auto->legal_id)->first();
                        if(empty($in_info) && $up_or_down==1){
                            $in = new TransactionIn();
                            $in->order_no= getOrderno();
                            $in->user_id=$auto->buy_user_id;
                            $in->price= $price_area;
                            $in->number= $number;
                            $in->status=1;
                            $in->create_time = time();
                            $in->currency= $auto->currency_id;
                            $in->legal= $auto->legal_id;
                            $in->save();
                            
                        }
                        $out_info = TransactionOut::where("currency",$auto->currency_id)->where("user_id",$auto->buy_user_id)->where("price",$price_area)->where("legal",$auto->legal_id)->first();
                        if(empty($out_info) && $up_or_down==0){
                            $out = new TransactionOut();
                            $out->order_no= getOrderno();
                            $out->user_id= $auto->buy_user_id;
                            $out->price= $price_area;
                            $out->number= $number;
                            $out->status=1;
                            $out->create_time = time();
                            $out->currency= $auto->currency_id;
                            $out->legal= $auto->legal_id;
                            $out->save();
                            
                        }
                        //买家法币扣除交易币增加
                        // $buy_wallet_legal = UsersWallet::where('user_id',$auto->buy_user_id)->where('currency',$auto->legal_id)->lockForUpdate()->first();
                        // if (!empty($buy_wallet_legal)){
                        //     $legal_decrement = bc_mul($new_complete->number,$new_complete->price,5);
                        //     $buy_wallet_legal->decrement('legal_balance',$legal_decrement);
                        // }
                        // $buy_wallet = UsersWallet::where('user_id',$auto->buy_user_id)->where('currency',$auto->currency_id)->lockForUpdate()->first();
                        // if (!empty($buy_wallet)){
                        //     $buy_wallet->increment('change_balance',$new_complete->number);
                        // }
                        // //卖家法币增加交易币扣除
                        // $sell_wallet_legal = UsersWallet::where('user_id',$auto->sell_user_id)->where('currency',$auto->legal_id)->lockForUpdate()->first();
                        // if (!empty($sell_wallet_legal)){
                        //     $legal_increment = bc_mul($new_complete->number,$new_complete->price,5);
                        //     $sell_wallet_legal->increment('legal_balance',$legal_increment);
                        // }
                        // $sell_wallet = UsersWallet::where('user_id',$auto->sell_user_id)->where('currency',$auto->currency_id)->lockForUpdate()->first();
                        // if (!empty($sell_wallet)){
                        //     $sell_wallet->decrement('change_balance',$new_complete->number);
                        // }
                        $this->info($auto->legal_name.'/'.$auto->currency_name.' 生成价格为 '.$new_complete->price.' 数量为 '.$new_complete->number.' 的交易记录-'.Carbon::now()->toDateTimeString());

                        $total = TransactionComplete::where('currency', $auto->currency_id)->where("user_id",$auto->buy_user_id)
                            ->where('legal', $auto->legal_id)
                            ->where('create_time', '>=', strtotime(date('Y-m-d')))
                            ->sum('number');
                        $data = [
                            'legal_id' => $auto->legal_id,
                            'currency_id' => $auto->currency_id,
                            'volume' => $total,
                            'now_price' => $new_complete->price
                        ];
                        
                        Transaction::pushDepth($auto->currency_id, $auto->legal_id);
                        
                        $now = time();
                        $nows = strtotime(date('Y-m-d H:i:00'),$now);
                        
                        MarketHour::batchEsearchMarket($new_complete->currency_name, $new_complete->legal_name, sctonum($new_complete->price), $number, $nows);
                        
                        //$now = microtime(true);
                        //$now = time();
                        //MarketHour::batchWriteMarketData($new_complete->currency, $new_complete->legal, $number, sctonum($new_complete->price), 1);
                        
                        // $data = [
                        //     'base-currency' => $new_complete->legal,
                        //     'quote-currency' => $new_complete->currency,
                        //     'period' => '1min',
                        //     'id' => $nows
                        // ];
                        //$info = MarketHour::getLastEsearchMarket($new_complete->currency,$new_complete->legal);
                        
                        // $info = MarketHour::getLastEsearchMarket('btc','usdt');
                        // var_dump($info);die;
                        //$info = MarketHour::getEsearchMarketById($new_complete->currency,$new_complete->legal,'1min',$nows);
                        
                         Log::info("机器人自动买卖3333333333333333333333");
                        //推送K线行情
                        $market_hour = MarketHour::getEsearchMarketById($new_complete->currency_name, $new_complete->legal_name, '1min', $nows);
                        
                        if(isset($market_hour['_source'])){
                            $source = $market_hour['_source'];
                            
                            $data["open"] = $source['open'];
                            $data["close"] = $source['close'];
                            $data["high"] = $source['high'];
                            $data["low"] = $source['low'];
                            
                            //写入行情数据
                            CurrencyQuotation::updateTodayPriceTable($data);//更新今日价格表
                            
                            $kline_data = [
                                'type' => 'kline',
                                'period' => '1min',
                                'currency_id' => $new_complete->currency,
                                'currency_name' => $new_complete->currency_name,
                                'legal_id' => $new_complete->legal,
                                'legal_name' => $new_complete->legal_name,
                                'symbol' => $new_complete->currency_name . '/' . $new_complete->legal_name,
                                'open' => $source['open'],
                                'close' => $source['close'],
                                'high' => $source['high'],
                                'low' => $source['low'],
                                'volume' => $source['vol'],
                                'time' => $source['id'] * 1000,
                            ];
                            
                            UserChat::sendText($kline_data); //写入行情数据
                            
                            CoinTradeHandel::dispatch($kline_data)->onQueue('coin_trade:handle');
                        }
                        
                        $currency = Currency::where("id",$auto->currency_id)->first();
                        $legal = Currency::where("id",$auto->legal_id)->first();
                        
                        $params = [
	                        'legal_id' => $new_complete->legal,
	                        'legal_name' => $new_complete->legal_name,
	                        'currency_id' => $new_complete->currency,
	                        'currency_name' => $new_complete->currency_name,
	                        'now_price' => $new_complete->price,
	                        'now' => $now
	                    ];
	                   
	                    //价格大于0才进行任务推送
	                    if (bc_comp($price_area, 0) > 0) {
	                        echo "\t" . date('Y-m-d H:i:s') . ' 推送' . $currency->name.'/'.$legal->name . '最新价格'. PHP_EOL;
	                        LeverUpdate::dispatch($params)->onQueue('lever:update');
	                        
	                        //LeverPushPrice::dispatch($params)->onQueue('lever:push:price');
	                    }
                        DB::commit();
                    }else{
                        DB::rollback();
                        return $this->error('没有当前价格区间');
                    }
                    $need_second=mt_rand($auto->min_need_second,$auto->max_need_second);
                    $this->info('机器人沉默:'.$need_second.'秒');
                    sleep($need_second);
                }
            }

        }catch (\Exception $exception){
             Log::info("机器人自动买卖报错".json_encode($exception->getMessage()));
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }
}
