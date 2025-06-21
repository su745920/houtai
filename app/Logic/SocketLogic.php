<?php

namespace App\Logic;

use Illuminate\Support\Carbon;
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\Cache;
use App\Models\Token;
use App\Models\{Hq1min,Hq5min,Hq15min,Hq30min,Hq60min,Hq1day,Hq1week,Hq1mon,HqPrice};
date_default_timezone_set('Asia/Shanghai'); // 设置为上海时区

class SocketLogic
{
    public static function messageHandler($event)
    {
        $message = $event->message;
        $client_id = $event->clientId;
        if (is_json($message)) {
            $message = json_decode($message, true);
            if (isset($message['event'])) {
                $event = ucfirst($message['event']);
                $method = "do{$event}";
                method_exists(self::class, $method) && call_user_func_array([self::class, $method], [$client_id, $message]);
            }
        } else {
            echo date('Y-m-d H:i:s') . ' 接收到未知的消息体:' . PHP_EOL;
            dump($message);
        }
    }

    /**
     * 用户登录
     * 
     * @param string $token
     * @param string $client_id
     * @return boolean
     */
    public static function doLogin($client_id, $message)
    {
        $now = Carbon::now();
        $token_and_symbol = explode(',',$message['params']);

        $token = $token_and_symbol[0];
        $token = Token::where('token', $token)
            ->where('time_out', '>', $now->getTimestamp())
            ->first();
        if (!$token) {
            Gateway::sendToClient($client_id, json_encode([
                    'event' => 'login_result',
                    'code' => -1,
                    'msg' => '登录失败',
                ], JSON_UNESCAPED_UNICODE)
            );
            return false;
        }
        Gateway::bindUid($client_id, $token->user_id);
        Gateway::sendToClient($client_id, json_encode([
                'event' => 'login_result',
                'code' => 1,
                'msg' => '登录成功',
            ], JSON_UNESCAPED_UNICODE)
        );
        if(count($token_and_symbol)>1){
            $symbol = $token_and_symbol[1];
            if(Cache::has('market_depth_'.$symbol)){
                $send_data = Cache::get('market_depth_'.$symbol); 
                Gateway::sendToClient($client_id, $send_data);
            }
        }
        return true;
    }

    /**
     * 用户登出
     * 
     * @param string $token
     * @param string $client_id
     * @return boolean
     */
    public static function doLogout($client_id, $message)
    {
        $now = Carbon::now();
        $token = $message['params'];
        $token = Token::where('token', $token)
            ->first();
        if (!$token) {
            Gateway::sendToClient($client_id, json_encode([
                    'event' => 'login_result',
                    'code' => -1,
                    'msg' => '登出失败',
                ], JSON_UNESCAPED_UNICODE)
            );
            return false;
        }
        Gateway::unbindUid($client_id, $token->user_id);
        Gateway::sendToClient($client_id, json_encode([
                'event' => 'login_result',
                'code' => 1,
                'msg' => '登出成功',
            ], JSON_UNESCAPED_UNICODE)
        );
        return true;
    }

    public static function doSub( $client_id, $message)
    {
        $params = $message['params'];
        Gateway::joinGroup($client_id, $params);
    }

    public static function doUnsub($client_id, $message)
    {
        $params = $message['params'];
        Gateway::leaveGroup($client_id, $params);
    }

    public static function sendMsg($data)
    {
        $send_data = json_encode($data);
        $type = $data['type'];
        $to = $data['to'] ?? 0;
        $data['self'] = 1;
        switch ($type) {
            case 'lever_trade':
            case 'lever_closed':
                if (!empty($to)) {
                    // 应先检测有没有订阅
                    $group = "{$type}";
                    $group_uid = Gateway::getUidListByGroup($group);
                    if (in_array($to, $group_uid)) {
                        Gateway::sendToUid($to, $send_data);
                    }
                }
                break;
            case 'kline':
                // no break;
            case 'match_trade';
                // no break;
            case 'market_depth':
                $symbol = $data['symbol'];
                $params = array_filter([$type, $symbol]);
                $group = implode('.', $params);
                // 插针替换处理
                 if($type == 'kline') {
                     try {
                     $reset_data = self::resetKlineHistoryData($data);
                        $send_data = json_encode($reset_data);
                      } catch (\Exception $e ) {
                          echo($e->getMessage());
                     }
                 }
                    
                Gateway::sendToGroup($group, $send_data);
                Cache::put('market_depth_'.$symbol, $send_data, Carbon::now()->addMinute(1));
                break;
            case 'daymarket':
                $symbol  = '';
                $params = array_filter([$type, $symbol]);
                $group = implode('.', $params);
                // 插针替换处理
                 try {
                    $reset_data = self::resetKlineData($data);
                    $send_data = json_encode($reset_data);
                  } catch (\Exception $e ) {
                     echo($e->getMessage());
                 }
                Gateway::sendToGroup($group, $send_data);
                break;
            case 'message':
                $symbol  = '';
                $params = array_filter([$type, $symbol]);
                $group = implode('.', $params);
                Gateway::sendToGroup($group, $send_data);
                break;
            default:       
        }
    }
    // 替换一天的事实数据
    public static function resetKlineData($data) {
         $currency_name = $data['symbol'];
        $symbol = str_replace('/','',$currency_name);
        $symbol = strtolower($symbol);
        // 插针插入- 判断分钟是否相等
         $id = strtotime(date('Y-m-d H:i:00'));
         $hbData = Hq1min::where("symbol",$symbol)->where('id',$id)->first();
         if($hbData) {
            // 随机浮动
            // 生成一个整数范围在10到500之间
            $close_integer = mt_rand(10, 500);
            // 将生成的整数转换为0.10至5之间的浮点数
            $close_random_float = $close_integer / 100;
            // 将结果调整到0.10至5之间
            $rand_close = $close_random_float / 100 + 0.10;
            
             // 生成一个整数范围在10到500之间
            $open_integer = mt_rand(10, 500);
            // 将生成的整数转换为0.10至5之间的浮点数
            $open_random_float = $open_integer / 100;
            // 将结果调整到0.10至5之间
            $rand_open = $open_random_float / 100 + 0.10;
            
            $data['close'] = bc_add($hbData->close, $rand_close,4);
            $data['open'] = bc_sub($hbData->open, $rand_open,4);
            $data['high'] = bc_add($hbData->high, $rand_close,4);
            $data['low'] = bc_sub($hbData->low, $rand_open,4);
            $data['id'] = $id;
            $data['rand_close'] = $rand_close;
         }else {
             // 判断是否设置了浮动价格
            $floatPrice = HqPrice::where("symbol",$symbol)->latest()->first();
            if($floatPrice) {
                 // 替换数据
                $open = bcadd($data['open'],$floatPrice->float_price);
                $close = bcadd($data['close'],$floatPrice->float_price);
                $high = bcadd($data['high'],$floatPrice->float_price);
                $low = bcadd($data['low'],$floatPrice->float_price);
                
                $data['close'] = (float)$close;
                $data['open'] = (float)$open;
                $data['high'] = (float)$high;
                $data['low'] = (float)$low;
            }
         }
         return $data;
    }
    // 替换kline历史数据推送插针数据
    public static function resetKlineHistoryData($data) {
          // 查询插针数据并替换
           $hbData = [];
           $currency_name = $data['symbol'];
           $symbol = str_replace('/','',$currency_name);
           $symbol = strtolower($symbol);
           $period = $data['period'];
           $id = strtotime(date('Y-m-d H:i:00'));
          switch ($period) {
              case '1min':
                  $hbData = Hq1min::where("symbol",$symbol)->where('id',$id)->first();
                  break;
              case '5min':
                  $hbData = Hq5min::where("symbol",$symbol)->where('id',$id)->first();
                  break;
              case '15min':
                  $hbData = Hq15min::where("symbol",$symbol)->where('id',$id)->first();
                  break;
              case '30min':
                  $hbData = Hq30min::where("symbol",$symbol)->where('id',$id)->first();
                  break;
              case '60min':
                  $id = strtotime(date('Y-m-d H:00:00'));
                  $hbData = Hq60min::where("symbol",$symbol)->where('id',$id)->first();
                  break;
              case '1day':
                  $id = strtotime(date('Y-m-d 00:00:00'));
                  $hbData = Hq1day::where("symbol",$symbol)->where('id',$id)->first();
                  break;
              case '1week':
                  $id = strtotime('monday this week');
                  $hbData = Hq1week::where("symbol",$symbol)->where('id',$id)->first();
                  break;
              case '1mon':
                  $id = strtotime(date('Y-m-1 00:00:00'));
                  $hbData = Hq1mon::where("symbol",$symbol)->where('id',$id)->first();
                  break;
          }
          if($hbData) {
              // 插针替换
              if(bc_div($data['time'],1000) == $v['id']) {
                $data['close'] = $hbData->close;
                $data['open'] = $hbData->open;
                $data['high'] = $hbData->high;
                $data['low'] = $hbData->low;
                $data['id'] = $hbData->id;
              }
              $data['aid'] = $hbData->id;
          }else {
             // 判断是否设置了浮动价格
                $floatPrice = HqPrice::where("symbol",$symbol)->latest()->first();
                if($floatPrice) {
                     // 替换数据
                    $open = bcadd($data['open'],$floatPrice->float_price);
                    $close = bcadd($data['close'],$floatPrice->float_price);
                    $high = bcadd($data['high'],$floatPrice->float_price);
                    $low = bcadd($data['low'],$floatPrice->float_price);
                    
                    $data['close'] = (float)$close;
                    $data['open'] = (float)$open;
                    $data['high'] = (float)$high;
                    $data['low'] = (float)$low;
                }  
          }
           return $data;
    }
}
