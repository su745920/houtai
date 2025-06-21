<?php

namespace App\Models;

class CurrencyMatchSimple extends Model
{
    public $timestamps = false;
    protected $table = 'currency_matches';
    protected static $USDTRate = null;

    protected $appends = [
        'legal_name',
        'currency_name',
        'market_from_name',
        'change',
        'volume',
        'now_price',
        'open',
        'close',
        'high',
        'low',
        'fiat_convert_cny',
        'logo',
        'plate_name',
        'optional_status',
        'legal_price',
    ];

    protected static $marketFromNames = [
        '无',
        '交易所',
        '火币接口',
        '机器人',
    ];

    public function legal()
    {
        return $this->belongsTo(Currency::class, 'legal_id', 'id')->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id')->withDefault();
    }

    public function quotation()
    {
        return $this->hasOne(CurrencyQuotation::class, 'legal_id', 'legal_id');
    }

    public function plate()
    {
        return $this->belongsTo(CurrencyPlate::class, 'plate_id', 'id');
    }

    public static function enumMarketFromNames()
    {
        return self::$marketFromNames;
    }

    public function getLegalPriceAttribute()
    {
        return $this->legal->price ?? 1;
    }

    public function getPlateNameAttribute()
    {
        return $this->plate->name ?? '';
    }

    public function getLogoAttribute()
    {
        return $this->currency->logo ?? '';
    }
    public function getMarketDataAttribute()
    {
        $legal_id = $this->attributes['legal_id'];
        $currency_id = $this->attributes['currency_id'];
        
        $data = [];
        if($legal_id && $currency_id){
            //$to = strtotime(date('Y-m-d H:i:00', time()));
            $to = strtotime(date('Y-m-d H:00:00', time()));
          
            $from = $to - 24 * 3600;
            
            //$data = MarketHour::getEsearchMarket($currency_id,$legal_id,'1min',$from,$to);
            $data = $this->klineMarket($currency_id,$legal_id,'60min',$from,$to);
            
        }
        // var_dump($currency_id.'||'.$legal_id.'||'.$from.'||'.$to);
        // var_dump($data);
        return $data;
    }
    
    public function klineMarket($currency_id,$legal_id,$period,$from,$to)
    {
        $result = [];
        //类型，1=15分钟，2=1小时，3=4小时,4=一天,5=分时,6=5分钟，7=30分钟,8=一周，9=一月,10=一年
        $period_list = [
            '1min' => '1min',
            '5min' => '5min',
            '15min' => '15min',
            '30min' => '30min',
            '60min' => '60min',
            '1H' => '60min',
            '1D' => '1day',
            '1W' => '1week',
            '1M' => '1mon',
            '1Y' => '1year',
            '1day' => '1day',
            '1week' => '1week',
            '1mon' => '1mon',
            '1year' => '1year',
        ];
        if ($from == null || $to == null) {
            return [
                'code' => -1,
                'msg' => 'error: from time or to time must be filled in',
                'data' => $result,
            ];
        }
        if ($from > $to) {
            return [
                'code' => -1,
                'msg' => 'error: from time should not exceed the to time.',
                'data' => $result,
            ];
        }
        $periods = array_keys($period_list);
        if ($period == '' || !in_array($period, $periods)) {
            return [
                'code' => -1,
                'msg' => 'error: period invalid',
                'data' => $result,
            ];
        }
        $period = $period_list[$period];
        $base_currency_model = Currency::where('id', $currency_id)
            ->first();
        $quote_currency_model = Currency::where('id', $legal_id)
            ->where("is_legal", 1)
            ->first();
        if (!$base_currency_model || !$quote_currency_model) {
            return [
                'code' => -1,
                'msg' => 'error: symbol not exist',
                'data' => null
            ];
        }
        $result = MarketHour::getEsearchMarket($base_currency_model['name'], $quote_currency_model['name'], $period, $from, $to);


        $result = array_map(function ($value) {
            $value['time'] = $value['id'] * 1000;
            $value['volume'] = $value['amount'];
            return $value;
        }, $result);
        return [
            'code' => 1,
            'msg' => 'success',
            'data' => $result
        ];
    }
    public function getSymbolAttribute()
    {
        return $this->getCurrencyNameAttribute() . '/' . $this->getLegalNameAttribute();
    }

    public function getMatchNameAttribute()
    {
        $clone_name = $this->currency->clone_name ?? '';
        if ($clone_name) {
            $currency_name = $clone_name;
        } else {
            $currency_name = $this->getCurrencyNameAttribute();
        }
        return strtolower($currency_name . $this->getLegalNameAttribute());
    }

    public function getLegalNameAttribute()
    {
        return $this->legal->name ?? '';
    }

    public function getCurrencyNameAttribute()
    {
        return $this->currency->name ?? '';
    }

    public function getMarketFromNameAttribute($value)
    {
        return self::$marketFromNames[$this->attributes['market_from']];
    }

    public function getCreateTimeAttribute($value)
    {
        return $value === null ? '' : date('Y-m-d H:i:s', $value);
    }

    public function getDaymarketAttribute()
    {
        $legal_id = $this->attributes['legal_id'];
        $currency_id = $this->attributes['currency_id'];
        CurrencyQuotation::unguard();
        $quotation = CurrencyQuotation::firstOrCreate([
            'legal_id' => $legal_id,
            'currency_id' => $currency_id,
        ], [
            'match_id' => $this->attributes['id'],
            'change' => '',
            'volume' => 0,
            'open' => 0,
            'close' => 0,
            'high' => 0,
            'low' => 0,
            'now_price' => 0,
            'add_time' => time(),
        ]);
        CurrencyQuotation::reguard();
        return $quotation;
    }

    public function getOptionalStatusAttribute()
    {
        $currency_match_id = $this->attributes['id'];
        // 检测用户有没有登录
        $user_id = Users::getUserId();
        if ($user_id) {
            if (UserMatch::where('user_id', $user_id)->where('currency_match_id', $currency_match_id)->exists()) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getChangeAttribute()
    {
        return $this->getDaymarketAttribute()->change;
    }

    public function getVolumeAttribute()
    {
        return $this->getDaymarketAttribute()->volume;
    }

    public function getNowPriceAttribute()
    {
        return $this->getDaymarketAttribute()->now_price;
    }

    public function getOpenAttribute()
    {
        return $this->getDaymarketAttribute()->open ?? 0;
    }

    public function getCloseAttribute()
    {
        return $this->getDaymarketAttribute()->close ?? 0;
    }

    public function getHighAttribute()
    {
        return $this->getDaymarketAttribute()->high ?? 0;
    }

    public function getLowAttribute()
    {
        return $this->getDaymarketAttribute()->low ?? 0;
    }

    public static function getMarketData()
    {
        $currency_match = self::with(['legal', 'currency'])
            ->where('market_from', 2)
            ->get();
        $huobi_symbols = HuobiSymbol::pluck('symbol')->all();
        $currency_match->transform(function ($item, $key) {
            $item->addHidden('currency');
            $item->addHidden('legal');
            $item->append('match_name');
            return $item;
        });
        //过滤掉不在火币中的交易对
        $currency_match = $currency_match->filter(function ($value, $key) use ($huobi_symbols) {
            return in_array($value->match_name, $huobi_symbols);
        });
        return $currency_match;
    }
    public static function getHuobiMatchs()
    {
        $currency_match = self::with(['legal', 'currency'])
            ->where('market_from', 2)
            ->get();
        $huobi_symbols = HuobiSymbol::pluck('symbol')->all();
        $currency_match->transform(function ($item, $key) {
            $item->addHidden('currency');
            $item->addHidden('legal');
            $item->append('match_name');
            return $item;
        });
        //过滤掉不在火币中的交易对
        $currency_match = $currency_match->filter(function ($value, $key) use ($huobi_symbols) {
            return in_array($value->match_name, $huobi_symbols);
        });
        return $currency_match;
    }
    
    public function getFiatConvertCnyAttribute()
    {
        return Currency::getUSDTRate();
    }
}