<?php

namespace App\Models;
use Illuminate\Support\Carbon;

class CurrencyQuotation extends Model
{
    protected $table = 'currency_quotation';
    public $timestamps = false;
    protected $appends = [
        'logo',
        'currency_name',
        'legal_name',
        'clone_name'
    ];

    public function updateData($data)
    {
        unset($data["symbol"]);
        self::unguard();
        $result = $this->fill($data)->save();
        self::reguard();
        return $result;
    }

    public static function getInstance($legal_id, $currency_id)
    {
        $quotation = self::where('legal_id', $legal_id)
            ->where('currency_id', $currency_id)
            ->first();
        if (!$quotation) {
            $currency_match = CurrencyMatch::where('legal_id', $legal_id)
                ->where('currency_id', $currency_id)
                ->first();
            $quotation = new self();
            $quotation->match_id = $currency_match->id ?? 0;
            $quotation->legal_id = $legal_id;
            $quotation->currency_id = $currency_id;
            $quotation->change = '';
            $quotation->volume = 0;
            $quotation->now_price = 0;
        }
        $quotation->add_time = time();
        $result = $quotation->save();
        return $quotation;
    }

    public static function updateTodayPriceTable($data)
    {

        $quotation = self::getInstance($data['legal_id'], $data['currency_id']);
        if (!isset($data['change'])) {
            //获得开盘价
            $open_price = TransactionComplete::getOpenPrice($data['currency_id'], $data['legal_id']);
            //计算涨跌百分比
            $change_ratio = bc_mul(bc_div(bc_sub($data['now_price'], $open_price), $open_price), 100, 4);
            if (bc_comp($change_ratio, '0') > 0) {
                $change_ratio = '+' . $change_ratio;
            }
            $data['change'] = $change_ratio;
        }
        $result = $quotation->updateData($data);
        $symbol = $quotation->currency_name . '/' . $quotation->legal_name;
        $quotation->setAttribute('symbol', $symbol);
        $quotation->setAttribute('period', '1day');
        $quotation->setAttribute('type', 'daymarket');
        $quotation->addHidden('id');
        //推送数据
        $send_data = $quotation->toArray();
        UserChat::sendText($send_data);
        return $result;
    }
    
    // 获取火币列表
    public static function getCurrencyQuotationList($type = -1) {
       date_default_timezone_set('Asia/Shanghai'); // 设置为上海时区
       $query = CurrencyQuotation::orderBy('id', 'asc')->where('is_show',1)->where('currency_id','!=',23);
       if($type < 0) {
           $data = $query->get();
       }else {
           $data = $query->where('coin_type',$type)->get();
       }
        // 获取一分钟插针数据进行替换
        foreach ($data as $k => $v) {
            $coinName = $v['currency_name'].$v['legal_name'];
            $coinName = strtolower($coinName);
            // 将时间换成成分钟
            $date = date('Y-m-d H:i',$v['add_time']);
            $date = strtotime($date);
            $hq1minData = Hq1min::getByIdAndSymbol($date,$coinName);
            if($hq1minData) {
                // 替换数据
                $data[$k]['close'] = (float)$hq1minData->close;
                $data[$k]['now_price'] = (float)$hq1minData->close;
                $data[$k]['open'] = (float)$hq1minData->open;
                $data[$k]['high'] = (float)$hq1minData->high;
                $data[$k]['low'] = (float)$hq1minData->low;
                $change = bc_div(($hq1minData->close - $hq1minData->open ),$hq1minData->open,4);
                $data[$k]['change'] = (float)$change*100;
            }else {
              // 判断是否设置了浮动价格
                $priceData = HqPrice::where("currency_id",$v['currency_id'])->orderBy('id', 'desc')->get();
                // 浮动价格替换
               if($priceData) {
                   foreach ($priceData as $pk => $pv) {
                      // 是否替换数据
                       $is_replace = false;
                       if($pv['end_time'] == 0 && $date >= $pv['start_time']) { // 没有终止时间，则可以直接替换
                           $is_replace = true;
                       }else
                        if($pv['start_time'] <= $date && $date < $pv['end_time']) {
                            $is_replace = true;
                        }
                        // 替换数据
                        if($is_replace) {
                             $open = bcadd($v['open'],$pv['float_price']);
                            // 判断是否是美股、外汇、贵金属
                            if($v['coin_type'] !== 0) {
                                $open = bcadd($v['last_close'],$pv['float_price']);
                            }
                            $close = bcadd($v['close'],$pv['float_price']);
                            $new_price = bcadd($v['now_price'],$pv['float_price']);
                            $high = bcadd($v['high'],$pv['float_price']);
                            $low = bcadd($v['low'],$pv['float_price']);
                            
                            $data[$k]['close'] = (float)$close;
                            $data[$k]['now_price'] = (float)$new_price;
                            $data[$k]['open'] = (float)$open;
                            $data[$k]['high'] = (float)$high;
                            $data[$k]['low'] = (float)$low;
                            $change = bc_div(($close - $open),$open,4);
                            $data[$k]['change'] = (float)$change*100;
                        }
                   }
               }
            }
        }
        return $data;
    }
    // 根据币种名字获取单个火币最新详情
    public static function getCurrencyQuotationDetail($currency_name) {
         date_default_timezone_set('Asia/Shanghai'); // 设置为上海时区
         $name = strtoupper($currency_name);
         $currency = Currency::where('name',$name)->first();
         if($currency) {
            $data = CurrencyQuotation::where('currency_id', $currency->id)->first();
            if($data) {
                 // 将时间换成成分钟
                $date = date('Y-m-d H:i',$data->add_time);
                $date = strtotime($date);
                $currency_name = strtolower($data->currency_name.$data->legal_name);
                $hq1minData = Hq1min::getByIdAndSymbol($date,$currency_name);
                // 插针数据替换
                if($hq1minData) {
                    // 替换数据
                    $data->close = (float)$hq1minData->close;
                    $data->now_price = (float)$hq1minData->close;
                    $data->open = (float)$hq1minData->open;
                    $data->high = (float)$hq1minData->high;
                    $data->low = (float)$hq1minData->low;
                    $change = bc_div(($hq1minData->close - $hq1minData->open ),$hq1minData->open,4);
                    $data->change = (float)$change*100;
                }else {
                    // 判断是否设置了浮动价格
                    $floatPrice = HqPrice::where("symbol",$currency_name)->latest()->first();
                        if($floatPrice) {
                         // 替换数据
                        $open = bcadd($data->open,$floatPrice->float_price);
                        // 判断是否是美股、外汇、贵金属
                        if($data->coin_type !== 0) {
                            $open = bcadd($data->last_close,$floatPrice->float_price);
                        }
                        $new_price = bcadd($data->now_price,$floatPrice->float_price);
                        $close = bcadd($data->close,$floatPrice->float_price);
                        $high = bcadd($data->high,$floatPrice->float_price);
                        $low = bcadd($data->low,$floatPrice->float_price);
                        
                        $data->close = (float)$close;
                        $data->open = (float)$open;
                        $data->now_price = (float)$new_price;
                        $data->high = (float)$high;
                        $data->low = (float)$low;
                        $change = bc_div(($close - $open),$open,4);
                        $data->change = (float)$change*100;
                    }  
                }
                return $data;
            }else {
                return null;
            }
         }else {
             return null;
         }
    }
    // 根据币种关联id获取单个火币最新详情
    public static function getCurrencyQuotationIdDetail($currency_id,$legal_id) {
        date_default_timezone_set('Asia/Shanghai'); // 设置为上海时区
        $data = CurrencyQuotation::where(['currency_id' => $currency_id,'legal_id' => $legal_id])->first();
         if($data) {
             // 将时间换成成分钟
            $date = date('Y-m-d H:i',$data->add_time);
            $date = strtotime($date);
            $currency_name = strtolower($data->currency_name.$data->legal_name);
            $hq1minData = Hq1min::getByIdAndSymbol($date,$currency_name);
            // 插针数据替换
            if($hq1minData) {
                // 替换数据
                $data->close = (float)$hq1minData->close;
                $data->now_price = (float)$hq1minData->close;
                $data->open = (float)$hq1minData->open;
                $data->high = (float)$hq1minData->high;
                $data->low = (float)$hq1minData->low;
                $change = bc_div(($hq1minData->close - $hq1minData->open ),$hq1minData->open,4);
                $data->change = (float)$change*100;
            }else {
                // 判断是否设置了浮动价格
                    $floatPrice = HqPrice::where("symbol",$currency_name)->latest()->first();
                        if($floatPrice) {
                         // 替换数据
                        $open = bcadd($data->open,$floatPrice->float_price);
                        // 判断是否是美股、外汇、贵金属
                        if($data->coin_type !== 0) {
                            $open = bcadd($data->last_close,$floatPrice->float_price);
                        }
                        $new_price = bcadd($data->now_price,$floatPrice->float_price);
                        $close = bcadd($data->close,$floatPrice->float_price);
                        $high = bcadd($data->high,$floatPrice->float_price);
                        $low = bcadd($data->low,$floatPrice->float_price);
                        
                        $data->close = (float)$close;
                        $data->open = (float)$open;
                        $data->now_price = (float)$new_price;
                        $data->high = (float)$high;
                        $data->low = (float)$low;
                        $change = bc_div(($close - $open),$open,4);
                        $data->change = (float)$change*100;
                    }  
            }
             return $data;
         }else {
             return null;
         }
        
    }
    // 根据币种关联id获取币种是否休市
    public static function getCurrencyQuotationIsLock($currency_id,$legal_id) {
        $data = CurrencyQuotation::where(['currency_id' => $currency_id,'legal_id' => $legal_id])->first();
        if($data && $data->is_lock == 1) {
            return true;
        }else {
            return false;
        }
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id')->withDefault();
    }

    public function legal()
    {
        return $this->belongsTo(Currency::class, 'legal_id', 'id')->withDefault();
    }
    
    public function getLogoAttribute()
    {
        return $this->currency()->value('logo');
    }

    public function getCurrencyNameAttribute()
    {
        return $this->currency()->value('name');
    }
    
     public function getCloneNameAttribute()
    {
        return $this->currency()->value('clone_name');
    }

    public function getLegalNameAttribute()
    {
        return $this->legal()->value('name');
    }
}
