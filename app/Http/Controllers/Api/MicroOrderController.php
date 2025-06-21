<?php

namespace App\Http\Controllers\Api;

use App\Models\InsuranceClaimApply;
use App\Models\InsuranceRule;
use App\Models\LeverTransaction;
use App\Models\MicroAmount;
use App\Models\Setting;
use App\Models\UsersInsurance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Logic\MicroTradeLogic;
use App\Models\Users;
use App\Models\CurrencyQuotation;
use App\Models\Currency;
use App\Models\MicroSecond;
use App\Models\UsersWallet;
use App\Models\MicroOrder;
use App\Models\MarketHour;
use App\Models\CurrencyMatch;
use App\Models\InsuranceType;
use App\Models\MicroNumbers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App;

class MicroOrderController extends Controller
{
    //下单数量快捷方式------2023-08-26
    public function getAmountList()
    {
        $seconds = MicroAmount::where('status', 1)
            ->get();
        return $seconds->count() > 0 ? $this->success($seconds) : $this->error($seconds);
    }
    
    /**
     * 取交易信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deal()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $legal_id = request()->input("legal_id");
        $currency_id = request()->input("currency_id");
        if (empty($legal_id) || empty($currency_id)) {
            return $this->error(trans('common.cscw').":(");
        }
        $matchId=CurrencyMatch::getMatchId($currency_id);
        $lever_share_limit = [
            'min' => 1,
            'max' => 0,
        ];
        $curreny = Currency::where('id', $currency_id)
            ->first();
        if ($curreny) {
            $lever_share_limit = array_merge($lever_share_limit, [
                'min' => $curreny->micro_min,
                'max' => $curreny->micro_max,
            ]);
        }
        //2023-08-29
        $user_lever = 0;
        if (!empty($user_id)) {
            $legal = UsersWallet::where("user_id", $user_id)->where("currency", $legal_id)->first();
            if ($legal) {
                $user_lever = $legal->micro_balance;
            }
        }

        //
        return $this->success([
             "matchId"=>$matchId,
            "user_lever" => $user_lever,
            "lever_share_limit" => $lever_share_limit,
            "ExRate" => Setting::getValueByKey('USDTRate', 6.5),
        ]);
    }
    /**
     * 取允许支付的币种
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayableCurrencies()
    {

        $cache_key_name = 'Currency/microNumbers';
        if (Cache::has($cache_key_name)){
            $currencies = Cache::get($cache_key_name);   
        }
        else{
            $currencies = Currency::with('microNumbers')
                ->where('is_micro', 1)
                ->get();
            Cache::put($cache_key_name, $currencies, Carbon::now()->addMinute(1));
        }

        $user = Users::getAuthUser();

        $currencies->transform(function ($item, $key) use ($user) {
            // 追加上险种
            $insurance_types = InsuranceType::where('currency_id', $item->id)
                ->get();
            $item->setAttribute('insurance_types', $insurance_types);
            // 追加上用户的钱包
            $wallet = UsersWallet::where('user_id', $user->id)
                ->where('currency', $item->id)
                ->first();
            if ($wallet) {
                $micro_with_insurance = bc_add($wallet->micro_balance, $wallet->insurance_balance);
                $wallet->setAttribute('micro_with_insurance', $micro_with_insurance);
            }
            $item->setAttribute('user_wallet', $wallet);
            // 追加上用户买的保险
            $user_insurance = UsersInsurance::where('user_id', $user->id)
                ->whereHas('insurance_type', function ($query) use ($item) {
                    $query->where('currency_id', $item->id);
                })->where('status', 1)->first();
            $item->setAttribute('user_insurance', $user_insurance);
            return $item;
        });
        return $this->success($currencies);
    }

    /**
     * 取到期时间
     */
    public function getSeconds()
    {
        $seconds = MicroSecond::where('status', 1)
            ->get();
        return $seconds->count() > 0 ? $this->success($seconds) : $this->error($seconds);
    }

    /**
     * 下单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if($user['status'] == 0){
            return $this->error(trans('login.gzhydjqlxkf'));
        }
        $type = $request->input('type', 0);
        $match_id = $request->input('match_id', 0);
        $currency_id = $request->input('currency_id', 23);
        $seconds = $request->input('seconds', 0);
        $number = $request->input('number', 0);
        if ($number<100){
            //throw new \Exception(trans('microorder.min100'));
        }
        $key = 'lan_type_'.$user_id;
        $lang = Cache::get($key);
        
        $currency_id =23;
        
        if(!$lang){
            $lang = 'en';
        }
        $validator = Validator::make($request->all(), [
            'match_id' => 'required|integer|min:1',
            'currency_id' => 'required|integer|min:1',
            'type' => 'required|integer|in:1,2',
            'seconds' => 'required|integer|min:1',
            'number' => 'required|numeric|min:100',
        ], [], [
            'match_id' => '交易对',
            'currency_id' => '支付币种',
            'type' => '下单类型',
            'seconds' => '到期时间',
            'number' => '投资数额',
        ]);

        try {
            //进行基本验证
            throw_if($validator->fails(), new \Exception($validator->errors()->first()));
            
            $insurance_start = Setting::getValueByKey('insurance_start','09:00');
            $insurance_end = Setting::getValueByKey('insurance_end','12:00');

            $insurance_start_datetime = Carbon::parse(date("Y-m-d {$insurance_start}:00"));
            $insurance_end_datetime = Carbon::parse(date("Y-m-d {$insurance_end}:00"));
            $use_insurance = 0;//是否使用受保金额
            $currency = Currency::find($currency_id);
            if(empty($currency)){
                throw new \Exception(trans('microorder.wzdgbz'));
            }
            /*//在受保时间段的话
            if (Carbon::now()->gte($insurance_start_datetime) && Carbon::now()->lte($insurance_end_datetime)) {
                if($currency->insurancable == 1){
                    $can_order = $this->canOrder($user_id, $currency_id, $number);
                    if($can_order !== true){
                        throw new \Exception("下单失败：{$can_order}");
                    }
                    $user_insurance = UsersInsurance::where('user_id', $user_id)
                        ->whereHas('insurance_type', function ($query) use ($currency_id) {
                            $query->where('currency_id', $currency_id);
                        })
                        ->where('status', 1)
                        ->where('claim_status', 0)
                        ->first();
                    $use_insurance = $user_insurance->insurance_type->type;//1,正向。2,反向。
                }
            }
            if (
                ($currency->insurancable != 1 || $use_insurance == 0) //如果当前不在受保时间段内或者所返币种不支持保险
                && $currency->micro_holdtrade_max > 0 
                && $this->getExistingOrderNumber($user_id, $currency_id) >= $currency->micro_holdtrade_max
            ) {
                throw new \Exception('下单失败:超过最大持仓笔数限制');
            }*/
            $currency_match = CurrencyMatch::find($match_id);
            $currency_quotation = CurrencyQuotation::where('match_id', $match_id)->first();
            throw_unless($currency_quotation, new \Exception(trans('microorder.dqwhqdhq')));
            //下单价格随机浮动，减少价格重复概率
            $decimal = 0;
            $faker = \Faker\Factory::create();
            if (stripos($currency_match->fluctuate_min, '.') !== false) {
                $fluctuate_min = rtrim($currency_match->fluctuate_min, '0'); //移除掉小数点后面右侧多余的0
                $fluctuate_min = rtrim($fluctuate_min, '.'); //如果是整数再移除掉小数点
                $decimal_index = stripos($fluctuate_min, '.'); //查找小数点的位置
                if ($decimal_index !== false) {
                    $decimal = strlen($fluctuate_min) - $decimal_index - 1;
                }
            }
            trim($currency_match->fluctuate_min, '0');
            $float_diff = $faker->randomFloat($decimal, $currency_match->fluctuate_min, $currency_match->fluctuate_max);
            

            $currencyName=$currency_match->currency_name;
            if ($currencyName=="TOETH"){
                $currencyName="BSV";
            }
            //$market = MarketHour::getLastEsearchMarket($currencyName, $currency_match->legal_name, '1min');
            //return $this->success($currencyName);
            //$price = $market[0]['close'] ?? $currency_quotation->now_price;
            
            //$price = $currency_quotation->now_price;


            $currencyName=strtolower($currencyName);
            $url="https://api.huobi.pro/market/detail/merged?symbol=".$currencyName."usdt";
            $result= $this->curl($url);
            $result=json_decode($result);
            $tick=$result->tick;
            $price=$tick->close;




//            if (mt_rand(0, 1)) {   2023-05-19注释掉
//                $price = bc_add($price, $float_diff);
//            } else {
//                $price = bc_sub($price, $float_diff);
//            }
            $order_data = [
                'user_id' => $user_id,
                'type' => $type,
                'match_id' => $match_id,
                'currency_id' => $currency_id,
                'seconds' => $seconds,
                'price' => $price,
                'number' => $number,
                'use_insurance' => $use_insurance,
            ];
           
            $order = MicroTradeLogic::addOrder($order_data);
            Redis::set('micro_tip_count', 1);
            return $this->success($order);
        } catch (\Throwable $th) {
            //return $this->error('File:' . $th->getFile() . ',Line:' . $th->getLine() . ',Message:' . $th->getMessage());
           
            return $this->error($th->getMessage());
            //return $this->error($th->getMessage());
        }
    }
    /**
     * 获得秒合约订单
     */
    public function getOrder(Request $request){
        
        $id = $request->input('id', 10);
        $user_id = Users::getUserId();
        
        $list = MicroOrder::where('user_id', $user_id)
            ->where('id', $id)
            ->get();
        return $this->success($list);
    }
    public function lists(Request $request)
    {
        try {
            $user_id = Users::getUserId();
            $limit = $request->input('limit', 10);
            $status = $request->input('status', -1);
            $match_id = $request->input('match_id', -1);
            $currency_id = $request->input('currency_id', -1);
            $lists = MicroOrder::where('user_id', $user_id)
                ->when($status <> -1, function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->when($match_id <> -1, function ($query) use ($match_id) {
                    $query->where('match_id', $match_id);
                })
                ->when($currency_id <> -1, function ($query) use ($currency_id) {
                    $query->where('currency_id', $currency_id);
                })
                ->orderBy('id', 'desc')
                ->paginate($limit);
            foreach($lists as $key => $val){
                $lists[$key]['profit_res'] = $val['end_price'] - $val['open_price'] - $val['fee'];
            }
            $lists->each(function ($item, $key) {
                return $item->append('remain_milli_seconds');
            });
            /*
            $results = $lists->getCollection();
            $results->transform(function ($item, $key) {
                return $item->append('remain_milli_seconds');
            });
            $lists->setCollection($results);
            */
            return $this->success($lists);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 获得秒合约下单规则
     */
    protected function getOrderRules($user_id, $currency_id, $user_insurance)
    {
        //默认规则

        $insurance_rules_arr = $user_insurance->insurance_rules_arr;
        if(count($insurance_rules_arr) > 0){
            foreach ($insurance_rules_arr as $rule){
                if($user_insurance->amount >= $rule['amount']){
                    return $rule;
                }
            }
        }
        return $rule = [
            'place_an_order_max' => 500,
            'existing_number' => 3
        ];
    }

    /**
     * 获得该币种交易中的秒合约订单
     */
    protected function getExistingOrderNumber($user_id, $currency_id){
        $count = MicroOrder::where('user_id', $user_id)
            ->where('status', MicroOrder::STATUS_OPENED)
            ->where('currency_id', $currency_id)
            ->count();
        return $count;
    }

    /**
     * 受保时间段是否可以下单
     */
    protected function canOrder($user_id, $currency_id, $number)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        //$user = Users::getById($user_id);
        //该币种是否购买了保险
        $user_insurance = UsersInsurance::where('user_id', $user_id)
            ->whereHas('insurance_type', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            })
            ->where('status', 1)
            ->where('claim_status', 0)
            ->first();
        if(!$user_insurance){
            return '尚未申购或理赔保险';
        }
        $insurance_type = $user_insurance->insurance_type;
        if($insurance_type->is_t_add_1 == 1){
            $user_insurance_created_at_date = Carbon::parse($user_insurance->created_at);
            if(Carbon::today()->isSameAs('Y-m-d',$user_insurance_created_at_date)){
                return '申购的保险T+1生效';
            }
        }

        //dd($insurance_type);
        //该用户该保险的对应的钱包。
        $user_wallet = UsersWallet::where('user_id', $user_id)
            ->where('currency', $insurance_type->currency_id)
            ->first();

        //受保资产为0不允许下单
        if($user_wallet->insurance_balance == 0){
            return '受保资产为零';
        }




        switch ($insurance_type->type){
            case 1:
                //受保金额小于等于此时不可以下单
                $defective_amount = bc_mul($user_insurance->amount ,bc_div($insurance_type->defective_claims_condition, 100));

                //正向险种，受保资产小于等于【条件1额度】，不允许下单
                if($user_wallet->insurance_balance <= $defective_amount){
                    return '受保资产小于等于可下单条件';
                }
                break;
            case 2:
                //反向险种，受保资产小于等于【条件2额度】，不允许下单
                if($user_wallet->insurance_balance <= $insurance_type->defective_claims_condition2){
                    return '您已超过持仓限制，暂停下单。';
                }
                break;
            default:
                return '未知的险种类型';
        }


        $order_rules = $this->getOrderRules($user_id, $currency_id, $user_insurance);
        //dd($order_rules);
        if($number > $order_rules['place_an_order_max']){
            return '超过最大持仓数量限制';
        }

        $getExistingOrderNumber = $this->getExistingOrderNumber($user_id, $currency_id);
        if($getExistingOrderNumber >= $order_rules['existing_number']){
            return '交易中的订单大于最大挂单数量';
        }

        return true;//可以下单
    }
    function curl($url,$postdata=[]) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $output;
    }


}
