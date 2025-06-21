<?php

namespace App\Utils\Workerman;
use Workerman\Protocols\Ws;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Lib\Timer;
use App\Models\{CurrencyMatch, CurrencyQuotation, MarketHour};
use App\Jobs\{EsearchMarket, LeverUpdate, SendMarket,HandleMicroTrade,CoinTradeHandel};
date_default_timezone_set('Asia/Shanghai'); // 设置为上海时区
class WsAlltickConnection
{
    protected $server_address = 'ws://quote.tradeswitcher.com/quote-b-ws-api';
    // protected $server_address = 'ws://api.huobi.br.com:443/ws'; //ws国内开发调试
    // protected $server_address = "wss://api.hbdm.com/linear-swap-ws";
    protected $server_ping_freq = 10; //服务器ping检测周期,单位秒
    protected $server_time_out = 2; //服务器响应超时
    protected $send_freq = 3; //写入和发送数据的周期，单位秒
    protected $micro_trade_freq = 1; //秒合约处理时间周期
    protected $worker_id;

    protected $events = [
        'onConnect',
        'onClose',
        'onMessage',
        'onError',
        'onBufferFull',
        'onBufferDrain',
    ];

    protected static $marketKlineData = [];
    protected static $marketDepthData = []; //盘口深度数据
    protected static $matchTradeData = []; //撮合交易全站交易

    /** @var \Workerman\Connection\AsyncTcpConnection|\Workerman\Connection\ConnectionInterface|null */
    protected $connection;

    protected $timer;

    protected $pingTimer;

    protected $sendKlineTimer;
    
    protected $getLastKlineTimer;
    
    protected $depthOtherTimer;

    protected $sendDepthTimer;

    protected $sendMatchTradeTimer;
    
    protected $microTradeHandleTimer;
    
    protected $handleTimer;

    protected $subscribed = [];

    protected $topicTemplate = [
        'sub' => [
            'market_kline' => 'market.$symbol.kline.$period', //K线
            'market_detail' => 'market.$symbol.detail',
            'market_depth' => 'market.$symbol.depth.$type', //盘口深度
            'market_trade' => 'market.$symbol.trade.detail', //成交的交易
        ],
    ];

    public function __construct($worker_id)
    {
        $this->worker_id = $worker_id;
        AsyncTcpConnection::$defaultMaxPackageSize = 1048576000;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 绑定所有事件到连接
     *
     * @return void
     */
    protected function bindEvent()
    {
        foreach ($this->events as $key => $event) {
            if (method_exists($this, $event)) {
                $this->connection && $this->connection->$event = [$this, $event];
                // echo '绑定' . $event . '事件成功' . PHP_EOL;
            }
        }
    }

    /**
     * 解除连接所有绑定事件
     *
     * @return void
     */
    protected function unBindEvent()
    {
        foreach ($this->events as $key => $event) {
            if (method_exists($this, $event)) {
                $this->connection && $this->connection->$event = null;
                //echo '解绑' . $event . '事件成功' . PHP_EOL;
            }
        }
    }

    public function getSubscribed($topic = null)
    {
        if (is_null($topic)) {
            return $this->subscribed;
        }
        return $this->subscribed[$topic] ?? null;
    }

    protected function setSubscribed($topic, $value)
    {
        $this->subscribed[$topic] = $value;
    }

    protected function delSubscribed($topic)
    {
        unset($this->subscribed[$topic]);
    }

    public function connect()
    {
        $this->connection = new AsyncTcpConnection($this->server_address.'?token='.config('websocket.alltick_client.token'));
        $this->bindEvent();
        // $this->connection->transport = 'ssl';
        $this->connection->websocketPingInterval = 10;
         $this->connection->websocketType = Ws::BINARY_TYPE_BLOB; // BINARY_TYPE_BLOB for text, BINARY_TYPE_ARRAYBUFFER for binary
        $this->connection->connect();
    }

    public function onConnect($con)
    {
        // //连接成功后定期发送ping数据包检测服务器是否在线
        $this->timer = Timer::add($this->server_ping_freq, [$this, 'ping'], [$this->connection], true);
        // //添加订阅事件代码
        $this->startSubscribe();
        if ($this->worker_id < 8) {
            $this->sendKlineTimer = Timer::add(5, [$this, 'writeMarketKline'], [], true);
            $this->depthTimer = Timer::add(7, [$this, 'sendDepthData'], [], true);
            $this->depthOtherTimer = Timer::add(8, [$this, 'sendOtherDepthData'], [], true);
            
            $this->getLastKlineTimer = Timer::add(18, [$this, 'getLastClose'], [], true);
            
        } elseif ($this->worker_id == 8) {
            
            // $this->sendMatchTradeTimer = Timer::add($this->send_freq, [$this, 'sendMatchTradeData'], [], true);
        }
        if ($this->worker_id == 5) {
            // $this->handleTimer = Timer::add($this->send_freq, [self::class, 'sendLeverHandle'], [], true);
        }
        if ($this->worker_id == 0) {
            // $this->microTradeHandleTimer = Timer::add($this->micro_trade_freq, [self::class, 'handleMicroTrade'], [], true);
        }
    }

    public function onClose($con)
    {
        echo $this->server_address . '连接关闭' . PHP_EOL;
        $path = base_path() . '/storage/logs/wss/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $this->server_address . '连接关闭' . PHP_EOL, 3, $path . $filename);
        //解除事件
        $this->timer && Timer::del($this->timer);
        $this->sendKlineTimer && Timer::del($this->sendKlineTimer);
        $this->pingTimer && Timer::del($this->pingTimer);
        $this->depthTimer && Timer::del($this->depthTimer);
        $this->depthOtherTimer && Timer::del($this->depthOtherTimer);
        $this->handleTimer && Timer::del($this->handleTimer);
        $this->getLastKlineTimer &&  Timer::del($this->getLastKlineTimer);

        $this->unBindEvent();
        unset($this->connection);
        $this->connection = null;
        $this->subscribed = null; //清空订阅
        echo '尝试重新连接' . PHP_EOL;
        $this->connect();
    }

    public function close($msg)
    {
        $path = base_path() . '/storage/logs/wss/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $msg, 3, $path . $filename);
        $this->connection->destroy();
    }

    protected function makeTopic($topic_template, $param)
    {
        $need_param = [];
        $match_count = preg_match_all('/\$([a-zA-Z_]\w*)/', $topic_template, $need_param);
        if ($match_count > 0 && count(reset($need_param)) > count($param)) {
            throw new \Exception('所需参数不匹配');
        }
        $diff = array_diff(next($need_param), array_keys($param));
        if (count($diff) > 0) {
            throw new \Exception('topic:' . $topic_template . '缺少参数：' . implode(',', $diff));
        }
        return preg_replace_callback('/\$([a-zA-Z_]\w*)/', function ($matches) use ($param) {
            extract($param);
            $value = $matches[1];
            return $$value ?? '';
        }, $topic_template);
    }

    public function onBufferFull()
    {
        echo 'buffer is full' . PHP_EOL;
    }

    /**
     * 订阅数据
     *
     * @param \Workerman\Connection\ConnectionInterface $con
     * @return void
     */
    protected function startSubscribe()
    {
        $currency_matches = CurrencyMatch::getAlltickMatchs();
        echo '订阅数量'.count($currency_matches);
        foreach ($currency_matches as $key => $currency_match) {
            $this->subscribe($currency_match);
        }
    }

    protected function subscribe($currency_match)
    {
        $periods = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week']; //['1day', '1min'];
        if ($this->worker_id < 8) {
            $period = $periods[$this->worker_id];
            // echo '进程'. $this->worker_id . '开始订阅' . $period . '数据' . PHP_EOL;
            $this->subscribeKline($period, $currency_match); //订阅k线行情
        } else {
            if ($this->worker_id == 8) {
                $this->subscribeMarketDepth($currency_match); //订阅盘口数据
                $this->subscribeMarketTrade($currency_match); //订阅全站交易数据
            }
        }
    }

    /**
     * 订阅Ｋ线
     *
     * @param string $period
     * @param \App\Models\CurrencyMatch $currency_match
     * @return void
     */
    protected function subscribeKline($period, $currency_match)
    {
        $param = [
            'symbol' => $currency_match->match_name,
            'period' => $period,
        ];
        $topic = $this->makeTopic($this->topicTemplate['sub']['market_kline'], $param);
        $sub_data = json_encode([
            'sub' => $topic,
            'id' => $topic,
            //'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
        ]);
        //未订阅过的才能订阅
        $subscribed_data = $this->getSubscribed($topic);
        $match_data = $subscribed_data['match'] ?? [];
        $match_data[] = $currency_match;
        $this->setSubscribed($topic, [
            'callback' => 'onMarketKline',
            'match' => $match_data,
        ]);
        if (is_null($subscribed_data)) {
            $this->connection->send($sub_data);
            error_log('send'.json_encode($sub_data));
        }
    }

    /**
     * 订阅盘口数据
     *
     * @param \Workerman\Connection\ConnectionInterface $con
     * @param \App\Models\CurrencyMatch $currency_match
     * @return void
     */
    protected function subscribeMarketDepth($currency_match)
    {
        $param = [
            'symbol' => $currency_match->match_name,
            'type' => 'step0',
        ];
        $topic = $this->makeTopic($this->topicTemplate['sub']['market_depth'], $param);
        $sub_data = json_encode([
            'sub' => $topic,
            'id' => $topic,
        ]);
        $subscribed_data = $this->getSubscribed($topic);
        $match_data = $subscribed_data['match'] ?? [];
        $match_data[] = $currency_match;
        $this->setSubscribed($topic, [
            'callback' => 'onMarketDepth',
            'match' => $match_data,
        ]);
        // 未订阅过的才能订阅
        if (is_null($subscribed_data)) {   
            $this->connection->send($sub_data);
        }
    }

    /**
     * 订阅全站交易(已完成)
     * @param \Workerman\Connection\ConnectionInterface $con
     * @param \App\Models\CurrencyMatch $currency_match
     * @return void
     */
    public function subscribeMarketTrade($currency_match)
    {
        $param = [
            'symbol' => $currency_match->match_name,
        ];
        $topic = $this->makeTopic($this->topicTemplate['sub']['market_trade'], $param);
        $sub_data = json_encode([
            'sub' => $topic,
            'id' => $topic,
        ]);
        $subscribed_data = $this->getSubscribed($topic);
        $match_data = $subscribed_data['match'] ?? [];
        $match_data[] = $currency_match;
        $this->setSubscribed($topic, [
            'callback' => 'onMatchTrade',
            'match' => $match_data,
        ]);
        // 未订阅过的才能订阅
        if (is_null($subscribed_data)) {
            $this->connection->send($sub_data);
        }
    }

    /**
     * 取消订阅已存在订阅
     *
     * @return void
     */
    protected function unsubExists()
    {
        $subscribed = $this->getSubscribed();
        foreach ($subscribed as $key => $value) {
            $this->connection->send(json_encode([
                'unsub' => $key,
                'id' => $key,
            ]));
        }
    }

    protected function unsub($currency_match)
    {
        $periods = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week']; //['1day', '1min'];
        if ($this->worker_id < 8) {
            $period = $periods[$this->worker_id];
            // echo '进程'. $this->worker_id . '开始取消订阅' . $period . '数据' . PHP_EOL;
            $this->unsubKline($period, $currency_match); //订阅k线行情
        } else {
            if ($this->worker_id == 8) {
                $this->unsubMarketDepth($currency_match); //订阅盘口数据
                $this->unsubMarketTrade($currency_match); //订阅全站交易数据
            }
        }
    }

    protected function unsubKline($period, $currency_match)
    {
        $param = [
            'symbol' => $currency_match->match_name,
            'period' => $period,
        ];
        $topic = $this->makeTopic($this->topicTemplate['sub']['market_kline'], $param);
        $subscribed_data = $this->getSubscribed($topic);
        if (!is_null($subscribed_data)) {
            $unsub_data = json_encode([
                'unsub' => $topic,
                'id' => $topic,
            ]);
            $this->connection->send($unsub_data);
        }
    }

    protected function unsubMarketDepth($currency_match)
    {
        $param = [
            'symbol' => $currency_match->match_name,
            'type' => 'step0',
        ];
        $topic = $this->makeTopic($this->topicTemplate['sub']['market_depth'], $param);
        $subscribed_data = $this->getSubscribed($topic);
        if (!is_null($subscribed_data)) {
            $unsub_data = json_encode([
                'unsub' => $topic,
                'id' => $topic,
            ]);
            $this->connection->send($unsub_data);
        }
    }

    protected function unsubMarketTrade($currency_match)
    {
        $param = [
            'symbol' => $currency_match->match_name,
        ];
        $topic = $this->makeTopic($this->topicTemplate['sub']['market_trade'], $param);
        $subscribed_data = $this->getSubscribed($topic);
        if (!is_null($subscribed_data)) {
            $unsub_data = json_encode([
                'unsub' => $topic,
                'id' => $topic,
            ]);
            $this->connection->send($unsub_data);
        }
    }

    /**
     * 订阅回调
     *
     * @param array $data
     * @return void
     */
    protected function onSubscribe($data)
    {
        if ($data->status == 'ok') {
            echo date('Y-m-d H:i:s ') . $data->subbed . '订阅成功' . PHP_EOL;
        } else {
            echo '订阅失败:' . $data->{'err-msg'} . PHP_EOL;
        }
    }
 
    /**
     * 取消订阅回调
     *
     * @param array $data
     * @return void
     */
    protected function onUnsubscribe($data)
    {
        if ($data->status == 'ok') {
            $this->delSubscribed($data->unsubbed);
            echo date('Y-m-d H:i:s ') . $data->unsubbed . '取消订阅成功' . PHP_EOL;
        } else {
            echo '取消订阅失败:' . $data->{'err-msg'} . PHP_EOL;
        }
    }


    /**
     * 订阅K线回调
     *
     * @param \Workerman\Connection\ConnectionInterface $con
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Collection $currency_matches
     * @return void
     */
    protected function onMarketKline($con, $data, $currency_matches)
    {   
        $topic = $data->ch;
        $msg = date('Y-m-d H:i:s') . ' 进程' . $this->worker_id . '接收' . $topic  . '行情' . PHP_EOL;
        list($name, $symbol, $detail_name, $period) = explode('.', $topic);
        foreach ($currency_matches as $key => $currency_match) {
            $tick = $data->tick;
            $market_data = [
                'id' => $tick->id,
                'period' => $period,
                'match_id' => $currency_match->id,
                'base-currency' => $currency_match->currency_name,
                'quote-currency' => $currency_match->legal_name,
                'open' => sctonum($tick->open),
                'close' => sctonum($tick->close),
                'high' => sctonum($tick->high),
                'low' => sctonum($tick->low),
                'vol' => sctonum($tick->vol),
                'amount' => sctonum($tick->amount),
            ];
            $kline_data = [
                'type' => 'kline',
                'period' => $period,
                'match_id' => $currency_match->id,
                'currency_id' => $currency_match->currency_id,
                'currency_name' => $currency_match->currency_name,
                'legal_id' => $currency_match->legal_id,
                'legal_name' => $currency_match->legal_name,
                'open' => sctonum($tick->open),
                'close' => sctonum($tick->close),
                'high' => sctonum($tick->high),
                'low' => sctonum($tick->low),
                'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                'volume' => sctonum($tick->amount),
                'time' => $tick->id * 1000,
            ];
            $symbol_key = $currency_match->currency_name . '.' . $currency_match->legal_name;
            self::$marketKlineData[$period][$symbol_key] = [
                'market_data' => $market_data,
                'kline_data' => $kline_data,
            ];
            
            if ($period == '1day') {
                //推送币种的日行情(带涨副)
                $change = $this->calcIncreasePair($kline_data);
                bc_comp($change, '0') > 0 && $change = '+' . $change;
                $id = strtotime(date('Y-m-d H:i:00'));
                //追加涨副等信息
                $daymarket_data = [
                    'type' => 'daymarket',
                    'change' => $change,
                    'now_price' => $market_data['close'],
                    'api_form' => 'huobi_websocket',
                    'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                    'id' => $id
                ];
                $kline_data = array_merge($kline_data, $daymarket_data);
                self::$marketKlineData[$period][$symbol_key]['kline_data'] = $kline_data;
            }
        }
    }
    
    
    public static function handleMicroTrade()
    {
        //self::$marketKlineData[$period][$key]['kline_data'] = $kline_data;
        $market_data = self::$marketKlineData;
        foreach ($market_data as $period => $data) {
            foreach ($data as $key => $symbol) {
                echo '秒合约时间:' . time() . ', Symbol:' . $key . '.' . $period . '数据' . PHP_EOL;
                  

                if ($period == '1min') {
                
    
                CurrencyQuotation::getInstance($symbol['kline_data']['legal_id'], $symbol['kline_data']['currency_id'])
                            ->updateData([
                                'now_price' => $symbol['kline_data']['close'],
                            ]);
                
                   
                    //处理秒合约
                    // $match_id=$symbol['kline_data']['match_id'];
                    // $c_m=CurrencyMatch::find($match_id);
                    // if($c_m->open_microtrade == 1){
                        HandleMicroTrade::dispatch($symbol['kline_data'])->onQueue('micro_trade:handle');
                        CoinTradeHandel::dispatch($symbol['kline_data'])->onQueue('coin_trade:handle');
                    // }

                } else {
                    continue;
                }
            }
        }
    }
    
    /**
     * 盘口回调
     *
     * @param \Workerman\Connection\ConnectionInterface $con
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Collection  $currency_matches
     * @return void
     */
    protected function onMarketDepth($con, $data, $currency_matches)
    {
        try {
            $topic = $data->ch;
            $limit = 10;
            $tick = $data->tick;
            $bids = array_slice($tick->bids, 0, $limit);
            $asks = array_slice($tick->asks, 0, $limit);
            krsort($asks);
            $asks = array_values($asks);
            // 将模拟克隆的交易对也添加上数据
            foreach ($currency_matches as $key => $currency_match) {
                $depth_data = [
                    'type' => 'market_depth',
                    'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                    'base-currency' => $currency_match->currency_name,
                    'quote-currency' => $currency_match->legal_name,
                    'currency_id' => $currency_match->currency_id,
                    'currency_name' => $currency_match->currency_name,
                    'legal_id' => $currency_match->legal_id,
                    'legal_name' => $currency_match->legal_name,
                    'bids' => $bids, //买入盘口
                    'asks' => $asks, //卖出盘口
                ];
                $symbol_key = $currency_match->currency_name . '.' . $currency_match->legal_name;
                self::$marketDepthData[$symbol_key] = $depth_data; 
            }
        } catch (\Throwable $th) {
           
        }       
    }

    /**
     * 撮合交易全站交易数据回调
     * @param \Workerman\Connection\ConnectionInterface $con
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Collection $currency_matches
     * @return void 
     */
    protected function onMatchTrade($con, $data, $currency_matches)
    {
        $topic = $data->ch;
        $data = $data->tick->data;
        foreach ($currency_matches as $key => $currency_match) {
            $symbol_key = $currency_match->currency_name . '.' . $currency_match->legal_name;
            $trade_data = [
                'type' => 'match_trade',
                'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                'base-currency' => $currency_match->currency_name,
                'quote-currency' => $currency_match->legal_name,
                'currency_id' => $currency_match->currency_id,
                'currency_name' => $currency_match->currency_name,
                'legal_id' => $currency_match->legal_id,
                'legal_name' => $currency_match->legal_name,
                'data' => $data,
            ];
            self::$matchTradeData[$symbol_key] = $trade_data; 
        }
    }

    /**
     * 发送股票盘口数据
     *
     * @return void
     */
    public function sendDepthData()
    {
        
        $list = CurrencyMatch::getAlltickMatchs();
        $symbol_list = [];
        foreach ($list as $k => $v) {
            if($v->market_from == 3) {
                // 股票
                 $symbol_list[] = [
                    "code" => coinResetName($v->currency_name)
                    ];
            }
        }
        $http_url = config('websocket.alltick_client.gp_url'); // 股票
        $token = config('websocket.alltick_client.token');
        $url = $http_url.'/depth-tick?token='.$token;
        
        $send_data = [
             "trace" => "3baaa938-f92c-4a74-a228-fd49d5e2f8bc-1678419653657",
             "data" => [
                 "symbol_list" => $symbol_list
              ]
        ];
        $query = urlencode(json_encode($send_data));
        $url = $url.'&query='.$query;
        
        try {
            $result = file_get_contents($url);
            $result = json_decode($result, true);
            if($result && $result['ret'] == 200) {
                $c_list = $result['data']['tick_list'];
                foreach ($c_list as $ck => $cv) {
                    $bids = [];
                    $asks = [];
                    foreach ($cv['bids'] as $bk => $bv) {
                        $bids[] = [(float)$bv['price'],(float)$bv['volume']];
                    }
                    foreach ($cv['asks'] as $ak => $av) {
                        $asks[] = [(float)$av['price'],(float)$av['volume']];
                    }
                    
                    // 判断币种是否相等
                    foreach ($list as $k => $v) {
                        if($cv['code'] == coinResetName($v->currency_name)) {
                            $depth_data = [
                                'base-currency' => $v->currency_name,
                                'currency_id' => $v->currency_id,
                                'currency_name' => $v->currency_name,
                                'legal_id' => $v->legal_id,
                                'legal_name' => $v->legal_name,
                                'quote-currency' => $v->legal_name,
                                'symbol' => $v->currency_name . '/' . $v->legal_name,
                                'type' => 'market_depth',
                                'bids' => $bids,
                                'asks' => $asks
                            ];
                            //  echo '发送股票盘口数据'.json_encode($depth_data)."\n";
                            SendMarket::dispatch($depth_data)->onQueue('market.depth');
                            break;
                        }
                        
                    }
                }
            }
        } catch (\Exception $e) {
            echo '发送股票盘口数据错误'."\n";
        }
        
        // $market_depth = self::$marketDepthData;
        // foreach ($market_depth as $depth_data) {
        //     SendMarket::dispatch($depth_data)->onQueue('market.depth');
        // }
        // self::$marketDepthData = [];
    }
    
    // 发送其它盘口数据
    public function sendOtherDepthData() {
        $list = CurrencyMatch::getAlltickMatchs();
        $symbol_list = [];
        foreach ($list as $k => $v) {
            if($v->market_from == 4) {
               // 外汇，贵金属
                $symbol_list[] = [
                    "code" => coinResetName($v->currency_name)
                    ];
            }
        }
        $http_url = config('websocket.alltick_client.other_url'); // 外汇,加密货币(数字币),商品(贵金属) HTTP接口API地址
        $token = config('websocket.alltick_client.token');
        $url = $http_url.'/depth-tick?token='.$token;

        $send_data = [
             "trace" => "3baaa938-f92c-4a74-a228-fd49d5e2f8bc-1678419653657",
             "data" => [
                 "symbol_list" => $symbol_list
              ]
        ];
        $query = urlencode(json_encode($send_data));
        $url = $url.'&query='.$query;
       
        try {
            $result = file_get_contents($url);
            $result = json_decode($result, true);
            if($result && $result['ret'] == 200) {
                $c_list = $result['data']['tick_list'];
                foreach ($c_list as $ck => $cv) {
                    $bids = [];
                    $asks = [];
                    foreach ($cv['bids'] as $bk => $bv) {
                        $bids[] = [(float)$bv['price'],(float)$bv['volume']];
                    }
                    foreach ($cv['asks'] as $ak => $av) {
                        $asks[] = [(float)$av['price'],(float)$av['volume']];
                    }
                    
                    // 判断币种是否相等
                    foreach ($list as $k => $v) {
                        if($cv['code'] == coinResetName($v->currency_name)) {
                            $depth_data = [
                                'base-currency' => $v->currency_name,
                                'currency_id' => $v->currency_id,
                                'currency_name' => $v->currency_name,
                                'legal_id' => $v->legal_id,
                                'legal_name' => $v->legal_name,
                                'quote-currency' => $v->legal_name,
                                'symbol' => $v->currency_name . '/' . $v->legal_name,
                                'type' => 'market_depth',
                                'bids' => $bids,
                                'asks' => $asks
                            ];
                            // echo '发送外汇，贵金属盘口数据'.json_encode($depth_data)."\n";
                            SendMarket::dispatch($depth_data)->onQueue('market.depth');
                            break;
                        }
                        
                    }
                }
            }
        } catch (\Exception $e ) {
            echo '发送外汇，贵金属盘口数据错误'."\n";
        }
    }
    /**
     * 发送全站交易数据
     *
     * @return void
     */
    public function sendMatchTradeData()
    {
        $market_trade = self::$matchTradeData;
        foreach ($market_trade as $trade_data) {
            SendMarket::dispatch($trade_data)->onQueue('send:match:trade');
        }
        self::$matchTradeData = []; //发送完清空,以避免重复发送相同的数据
    }

    public function onMessage($con, $data)
    {
        
        //  $this->onPong($con, $data);
        
        // echo 'data1:'.$data ."\n";
        // // $data = gzdecode($data);
        $data = json_decode($data, false, 512, JSON_BIGINT_AS_STRING); //对特大整数的处理，避免转换成科学记数法
        
        if(isset($data->data)) {
             $send_data = $data->data;
            $send_data->type = 'ceshi';
            $send_data = json_decode(json_encode($send_data),true);
            // echo json_encode($send_data)."\n";
            
             SendMarket::dispatch($send_data)->onQueue('kline.all');
        
        }
       
        if(isset($data->data)) {
            // echo json_encode($data->data) . "\n";
        }
        // if (isset($data->ret)) {
        //     $this->onPong($con, $data);
        // } elseif (isset($data->ret)) {
        //     $this->onPing($con, $data);
        // } elseif (isset($data->id) && isset($data->subbed)) {
        //     $this->onSubscribe($data);
        // } elseif (isset($data->id) && isset($data->unsubbed)) {
        //     $this->onUnSubscribe($data);
        // } else {
        //     $this->onData($con, $data);
        // }
    }

    protected function onData($con, $data)
    {
        if (isset($data->ch)) {
            $subscribed = $this->getSubscribed($data->ch);
            if ($subscribed != null) {
                //调用回调处理
                $callback = $subscribed['callback'];
                $this->$callback($con, $data, $subscribed['match']);
            } else {
                //不在订阅中的数据
            }
        } else {
            echo '未知数据' . PHP_EOL;
            var_dump($data);
        }
    }

    public static function sendLeverHandle()
    {
        //echo date('Y-m-d H:i:s') . '定时器取价格' . PHP_EOL;
        $now = microtime(true);
        $master_start = microtime(true);
        //echo str_repeat('=', 80) . PHP_EOL;
        //echo date('Y-m-d H:i:s') . '开始发送价格到杠杆交易系统' . PHP_EOL;
        //echo '{' . PHP_EOL;
        try {
            $market_kiline = self::$marketKlineData['1day'] ?? null;
            if ($market_kiline) {
                foreach ($market_kiline as $key => $value) {
                    $kline_data = $value['kline_data'];
                    $start = microtime(true);
                    //echo "\t" . date('Y-m-d H:i:s') . ' 发送' . $key . ',价格:' . $kline_data['close'] . PHP_EOL;
                    $params = [
                        'legal_id' => $kline_data['legal_id'],
                        'legal_name' => $kline_data['legal_name'],
                        'currency_id' => $kline_data['currency_id'],
                        'currency_name' => $kline_data['currency_name'],
                        'now_price' => $kline_data['close'],
                        'now' => $now
                    ];
                    //价格大于0才进行任务推送
                    if (bc_comp($kline_data['close'], '0') > 0) {
                        LeverUpdate::dispatch($params)->onQueue('lever:update');
                    }
                    $end = microtime(true);
                    //echo "\t" . date('Y-m-d H:i:s') . $key . '处理完成,耗时' .($end - $start) . '秒' . PHP_EOL;
                }
                $master_end = microtime(true);
                //echo '}' . PHP_EOL;
                //echo date('Y-m-d H:i:s') . '杠杆交易系统处理完成,耗时' . ($master_end - $master_start) . '秒' . PHP_EOL;
                //echo str_repeat('=', 80) . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo str_repeat('=', 80) . PHP_EOL;
            echo 'File:' . $e->getFile() . PHP_EOL;
            echo 'Line:' . $e->getLine() . PHP_EOL;
            echo 'Message:' . $e->getMessage() . PHP_EOL;
            echo str_repeat('=', 80) . PHP_EOL;
        }
    }

    public function writeMarketKline()
    {
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
        "trace" => "c2a8a146-a647-4d6f-ac07-8c4805bf0b74",
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
        try {
             $result = curl_post($gp_url,$gp_post_data,false,true,array('trace: c2a8a146-a647-4d6f-ac07-8c4805bf0b74'));
            if($result && $result['ret'] == 200) {
                foreach ($gp_list as $k => $v) {
                    foreach ($result['data']['kline_list'] as $rk => $rv) {
                        if($v['currency_name'] == $rv['code']) {
                             //  //存入数据库
                             $kline_data = $rv['kline_data'][0];
                            $currency_uotation = CurrencyQuotation::getInstance($v['legal_id'], $v['currency_id']);
                            $change = $this->calcIncreasePair($currency_uotation->last_close,$kline_data['close_price']);
                            bc_comp($change, '0') > 0 && $change = '+' . $change;
                            // -判断是否休市：如果当前分钟时间比推送时间大则属于休市状态
                            $current_timestamp = strtotime(date("Y-m-d H:i:0"));
                            // 大于1分钟为准
                            if($current_timestamp - (int)$rv['kline_data'][0]['timestamp'] > 60) {
                                $is_lock = 1; // 休市
                            }else {
                                $is_lock = 0; // 开市
                            }
                            $currency_uotation->updateData([
                                'change' => $change,
                                'now_price' => $kline_data['close_price'],
                                'volume' => $kline_data['volume'],
                                'open' => $kline_data['open_price'],
                                'close' => $kline_data['close_price'],
                                'high' => $kline_data['high_price'],
                                'low' => $kline_data['low_price'],
                                'is_lock' => $is_lock
                            ]);
                           
                            // 推送股票
                            $day_kline['api_form'] = 'alltick_websocket';
                            $day_kline['change'] = (float)$change;
                            $day_kline['volume'] = (float)$kline_data['volume'];
                            $day_kline['open'] = (float)$kline_data['open_price'];
                            $day_kline['close'] = (float)$kline_data['close_price'];
                            $day_kline['high'] = (float)$kline_data['high_price'];
                            $day_kline['low'] = (float)$kline_data['low_price'];
                            $day_kline['type'] = 'kline';
                            $day_kline['symbol'] = $v['currency_name'].'/USDT';
                            $day_kline['currency_id'] = $v['currency_id'];
                            $day_kline['legal_id'] = $v['legal_id'];
                            $day_kline['currency_name'] = $v['currency_name'];
                            $day_kline['legal_name'] = $v['legal_name'];
                            $day_kline['match_id'] = $v['id'];
                            $day_kline['now_price'] = (float)$kline_data['close_price'];
                            $day_kline['time'] = (int)$kline_data['timestamp']*1000;
                            
                            $id = strtotime(date('Y-m-d H:i:00'));
                            $day_kline['id'] = $id;
                            //  echo '发送股票k数据'.json_encode($day_kline)."\n";
                            // 一分钟推送
                             $day_kline['period'] = '1min';
                             SendMarket::dispatch($day_kline)->onQueue('kline.all');
                            // 15分钟推送
                            if((int)$kline_data['timestamp']%5 == 0) {
                               $day_kline['period'] = '15min';
                               SendMarket::dispatch($day_kline)->onQueue('kline.all');
                            }
                            // 30分钟推送
                            if((int)$kline_data['timestamp']%30 == 0) {
                               $day_kline['period'] = '30min';
                               SendMarket::dispatch($day_kline)->onQueue('kline.all');
                            }
                            // 1小时推送
                            if((int)$kline_data['timestamp']%60 == 0) {
                               $day_kline['period'] = '60min';
                               SendMarket::dispatch($day_kline)->onQueue('kline.all');
                            }
                            $day_kline['type'] = 'daymarket';
                            $day_kline['period'] = '1day';
                            SendMarket::dispatch($day_kline)->onQueue('kline.all'); // 发送一天的
                            
                             // 更新杠杆交易价格
                            try {
                                $params = [
                                    'legal_id' => $v['legal_id'],
                                    'legal_name' => $v['legal_name'],
                                    'currency_id' => $v['currency_id'],
                                    'currency_name' => $v['currency_name'],
                                    'now_price' => (float)$kline_data['close_price'],
                                    'now' => (float)$kline_data['timestamp']
                                ];
                                //价格大于0才进行任务推送
                                if (bc_comp($kline_data['close_price'], '0') > 0) {
                                    LeverUpdate::dispatch($params)->onQueue('lever:update');
                                }
                                // echo '更新杠杆交易价格'.json_encode($params)."\n";
                            } catch (\Exception $e) {
                                echo '更新杠杆交易价格报错了！！！！！！！！！！！！！！！'."\n";
                            }
                            // 更新秒合约交易价格
                            try {
                                CurrencyQuotation::getInstance($v['legal_id'], $v['currency_id'])
                                ->updateData([
                                    'now_price' => (float)$kline_data['close_price'],
                                ]);
                                //处理秒合约
                                HandleMicroTrade::dispatch($day_kline)->onQueue('micro_trade:handle');
                                CoinTradeHandel::dispatch($day_kline)->onQueue('coin_trade:handle');
                                // echo '更新秒合约交易价格'.$kline_data['close_price']."\n";
                            } catch (\Exception $e) {
                                echo '更新秒合约交易价格报错了！！！！！！！！！！！！！！！'."\n";
                            }
                            break;
                        }
                    }
                   
                }
            }
        } catch (\Exception $e) {
            echo '股票k报错：'.$e->getMessage()."\n";
        }
        // 其它
        $other_post_list = [
        "trace" => "c2a8a146-a647-4d6f-ac07-8c4805bf0b52",
         "data" => [
             "data_list" => []
             ]
        ];
        foreach ($other_list as $k => $v) {
            array_push(
                $other_post_list['data']['data_list'],
                [
                    "code" => coinResetName($v['currency_name']),
                    "kline_type" => 1,
                    "kline_timestamp_end" => 0,
                    "query_kline_num" => 1,
                    "adjust_type" => 0
                  ]);
        }
        $other_url = config('websocket.alltick_client.other_url')."/batch-kline?token=".config('websocket.alltick_client.token');
        try {
             $result = curl_post($other_url,$other_post_list,false,true,array('trace: c2a8a146-a647-4d6f-ac07-8c4805bf0b52'));
            if($result && $result['ret'] == 200) {
                foreach ($other_list as $k => $v) {
                    foreach ($result['data']['kline_list'] as $rk => $rv) {
                        if(coinResetName($v['currency_name']) == $rv['code']) {
                             //存入数据库
                            $kline_data = $rv['kline_data'][0];
                            $currency_uotation = CurrencyQuotation::getInstance($v['legal_id'], $v['currency_id']);
                            $change = $this->calcIncreasePair($currency_uotation->last_close,$kline_data['close_price']);
                            bc_comp($change, '0') > 0 && $change = '+' . $change;
                            // -判断是否休市：如果当前分钟时间比推送时间大则属于休市状态
                            $current_timestamp = strtotime(date("Y-m-d H:i:0"));
                            // 大于1分钟为准
                            if($current_timestamp - (int)$rv['kline_data'][0]['timestamp'] > 60) {
                                $is_lock = 1; // 休市
                            }else {
                                $is_lock = 0; // 开市
                            }
                            $currency_uotation->updateData([
                                'change' => $change,
                                'now_price' => $kline_data['close_price'],
                                'volume' => $kline_data['volume'],
                                'open' => $kline_data['open_price'],
                                'close' => $kline_data['close_price'],
                                'high' => $kline_data['high_price'],
                                'low' => $kline_data['low_price'],
                                 'is_lock' => $is_lock
                            ]);
                            
                            // 推送其它
                            $day_kline['api_form'] = 'alltick_websocket';
                            $day_kline['change'] = (float)$change;
                            $day_kline['volume'] = (float)$kline_data['volume'];
                            $day_kline['open'] = (float)$kline_data['open_price'];
                            $day_kline['close'] = (float)$kline_data['close_price'];
                            $day_kline['high'] = (float)$kline_data['high_price'];
                            $day_kline['low'] = (float)$kline_data['low_price'];
                            $day_kline['type'] = 'kline';
                            $day_kline['symbol'] = $v['currency_name'].'/USDT';
                            $day_kline['currency_id'] = $v['currency_id'];
                            $day_kline['legal_id'] = $v['legal_id'];
                            $day_kline['currency_name'] = $v['currency_name'];
                            $day_kline['legal_name'] = $v['legal_name'];
                            $day_kline['match_id'] = $v['id'];
                            $day_kline['now_price'] = (float)$kline_data['close_price'];
                            $day_kline['time'] = (int)$kline_data['timestamp']*1000;
                            
                            $id = strtotime(date('Y-m-d H:i:00'));
                            $day_kline['id'] = $id;
                            // 一分钟推送
                             $day_kline['period'] = '1min';
                             SendMarket::dispatch($day_kline)->onQueue('kline.all');
                            // 15分钟推送
                            if((int)$kline_data['timestamp']%5 == 0) {
                               $day_kline['period'] = '15min';
                               SendMarket::dispatch($day_kline)->onQueue('kline.all');
                            }
                            // 30分钟推送
                            if((int)$kline_data['timestamp']%30 == 0) {
                               $day_kline['period'] = '30min';
                               SendMarket::dispatch($day_kline)->onQueue('kline.all');
                            }
                            // 1小时推送
                            if((int)$kline_data['timestamp']%60 == 0) {
                               $day_kline['period'] = '60min';
                               SendMarket::dispatch($day_kline)->onQueue('kline.all');
                            }
                            // 一天推送
                            $day_kline['type'] = 'daymarket';
                            $day_kline['period'] = '1day';
                            SendMarket::dispatch($day_kline)->onQueue('kline.all');
                            //  echo '发送外汇贵金属k数据'.json_encode($day_kline)."\n";
                             // 更新杠杆交易价格
                            try {
                                $params = [
                                    'legal_id' => $v['legal_id'],
                                    'legal_name' => $v['legal_name'],
                                    'currency_id' => $v['currency_id'],
                                    'currency_name' => $v['currency_name'],
                                    'now_price' => (float)$kline_data['close_price'],
                                    'now' => (float)$kline_data['timestamp']
                                ];
                                //价格大于0才进行任务推送
                                if (bc_comp($kline_data['close_price'], '0') > 0) {
                                    LeverUpdate::dispatch($params)->onQueue('lever:update');
                                }
                                // echo '更新杠杆交易价格'.json_encode($params)."\n";
                            } catch (\Exception $e) {
                                echo '更新杠杆交易价格报错了！！！！！！！！！！！！！！！'."\n";
                            }
                            // 更新秒合约交易价格
                            try {
                                CurrencyQuotation::getInstance($v['legal_id'], $v['currency_id'])
                                ->updateData([
                                    'now_price' => (float)$kline_data['close_price'],
                                ]);
                                //处理秒合约
                                HandleMicroTrade::dispatch($day_kline)->onQueue('micro_trade:handle');
                                CoinTradeHandel::dispatch($day_kline)->onQueue('coin_trade:handle');
                                // echo '更新秒合约交易价格'.$kline_data['close_price']."\n";
                            } catch (\Exception $e) {
                                echo '更新秒合约交易价格报错了！！！！！！！！！！！！！！！'."\n";
                            }
                            break;
                        }
                    }
                   
                }
            }
        } catch (\Exception $e) {
            echo '外汇贵金属报错：'.$e->getMessage()."\n";
        }
        echo '更新数据表' . PHP_EOL;
    }
    
    // 获取k线条历史记录前一天的收盘价
    public function getLastClose() {
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
        "trace" => "c2a8a146-a647-4d6f-ac07-8c4805bf6p98",
         "data" => [
             "data_list" => []
             ]
        ];
        foreach ($gp_list as $k => $v) {
            array_push(
                $gp_post_data['data']['data_list'],
                [
                    "code" => $v['currency_name'],
                    "kline_type" => 8,
                    "kline_timestamp_end" => 0,
                    "query_kline_num" => 2,
                    "adjust_type" => 0
                  ]);
        }
        $gp_url = config('websocket.alltick_client.gp_url')."/batch-kline?token=".config('websocket.alltick_client.token');
        try {
            $result = curl_post($gp_url,$gp_post_data,false,true,array('trace: c2a8a146-a647-4d6f-ac07-8c4805bf6p98'));
            if($result && $result['ret'] == 200) {
                foreach ($gp_list as $k => $v) {
                    foreach ($result['data']['kline_list'] as $rk => $rv) {
                        if($v['currency_name'] == $rv['code']) {
                             //  //存入数据库
                             $kline_data = $rv['kline_data'][0]; // 昨天的数据
                             // 更新昨天的收盘价
                             CurrencyQuotation::getInstance($v['legal_id'], $v['currency_id'])
                            ->updateData([
                                'last_close' => $kline_data['close_price']
                            ]);
                            break;
                        }
                    }
                   
                }
            }
        } catch (\Exception $e) {
            echo '昨天股票k报错'.$e->getMessage()."\n";
        }
        // 其它
        $other_post_list = [
        "trace" => "c2a8a146-a647-4d6f-ac07-8c4805bf87po",
         "data" => [
             "data_list" => []
             ]
        ];
        foreach ($other_list as $k => $v) {
            array_push(
                $other_post_list['data']['data_list'],
                [
                    "code" => coinResetName($v['currency_name']),
                    "kline_type" => 8,
                    "kline_timestamp_end" => 0,
                    "query_kline_num" => 2,
                    "adjust_type" => 0
                  ]);
        }
        $other_url = config('websocket.alltick_client.other_url')."/batch-kline?token=".config('websocket.alltick_client.token');
        try {
            $result = curl_post($other_url,$other_post_list,false,true,array('trace: c2a8a146-a647-4d6f-ac07-8c4805bf87po'));
            if($result && $result['ret'] == 200) {
                foreach ($other_list as $k => $v) {
                    foreach ($result['data']['kline_list'] as $rk => $rv) {
                        if(coinResetName($v['currency_name']) == $rv['code']) {
                              //  //存入数据库
                             $kline_data = $rv['kline_data'][0]; // 昨天的数据
                             CurrencyQuotation::getInstance($v['legal_id'], $v['currency_id'])
                            ->updateData([
                                'last_close' => $kline_data['close_price']
                            ]);
                            break;
                        }
                    }
                   
                }
            }
        } catch (\Exception $e) {
            echo '昨天外汇贵金属昨天报错'."\n";
        }
        echo '昨天更新数据表' . PHP_EOL;
    }

    protected function calcIncreasePair($last_close,$close)
    {
        $change_value = bc_sub($close,$last_close);
        $change = bc_mul(bc_div($change_value, $last_close), 100, 6);
        $change = round($change,2);
        return $change;
    }

    //心跳响应
    protected function onPong($con, $data)
    {
        echo '收到心跳包,PING:' . $data . PHP_EOL;
        // $send_data = [
        //     'pong' => $data->ping,
        // ];
        // $send_data = json_encode($data);
        // $con->send($send_data);
        //echo '已进行心跳响应' . PHP_EOL;
    }

    public function ping($con)
    {
        echo '收到心跳包,PING:' . PHP_EOL;
        $ping = time();
        //echo '进程' . $this->worker_id . '发送ping服务器数据包,ping值:' . $ping . PHP_EOL;
        $send_data = '{"cmd_id":22004,"seq_id":106254111,"trace":"3baaa938-f92c-4a74-a228-fd49d57p","data":{"symbol_list":[{"code":"1288.HK",
                "depth_level": 5,},{"code":"AUDUSD"}]}}';
        $con->send($send_data);
        // $this->pingTimer = Timer::add($this->server_time_out, function () use ($con) {
        //     $msg = '进程' . $this->worker_id . '服务器响应超时,连接关闭' . PHP_EOL;
        //     echo $msg;
        //     $this->close($msg);
        // }, [], false);
    }

    protected function onPing($con, $data)
    {
        $this->pingTimer && Timer::del($this->pingTimer);
        $this->pingTimer = null;
        //echo '进程' . $this->worker_id . '服务器正常响应中,pong:' . $data->pong. PHP_EOL;
    }
}
