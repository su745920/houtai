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
use Illuminate\Support\Facades\DB;

use App;

class MicroOrderV2Controller extends Controller
{
    //下单2023-10-11
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
        $currency_id = $request->input('currency_id', 32);
        $legal_id = $request->input('legal_id', 23);
        
        //  $match_id=61;
        // if ($currency_id==35){
        //     $match_id=64;
        // }
        
        // 判断是否开启了实名认证校验
        if(Setting::getValueByKey("is_open_transaction") === 1) {
             // 判断是否高级实名认证
            $advanced_review_status = 0;
            $real_data = DB::table('user_real')->where('user_id',$user_id)->orderBy("id","desc")
                ->first();
            if (!empty($real_data)){
                if ($real_data->advanced_user == 2){
                    $advanced_review_status = 2;
                }
            }
            if($advanced_review_status !== 2) {
                return $this->error(trans('login.qsmrz'));
            }
        }
        
        $po = CurrencyMatch::where('currency_id', $currency_id)->select(['id'])->first();
        $match_id=$po->id;
        
        
        $seconds = $request->input('seconds', 0);
        $number = $request->input('number', 0);
        if ($number<100){
            //throw new \Exception(trans('microorder.min100'));
        }
        $key = 'lan_type_'.$user_id;
        $lang = Cache::get($key);



        if(!$lang){
            $lang = 'en';
        }
        $validator = Validator::make($request->all(), [

            'currency_id' => 'required|integer|min:1',
            'type' => 'required|integer|in:1,2',
            'seconds' => 'required|integer|min:1',
            'number' => 'required|numeric|min:1',
        ], [], [

            'currency_id' => '支付币种',
            'type' => '下单类型',
            'seconds' => '到期时间',
            'number' => '投资数额',
        ]);
        
        // 判断是否休市
        $is_lock = CurrencyQuotation::getCurrencyQuotationIsLock($currency_id,$legal_id);
        if($is_lock) {
            return $this->error(trans('common.stop'));
        }
        
        // 判断是否存在未结算的是订单
        $order = MicroOrder::where('user_id',$user_id)->where('status',1)->first();
        if($order) {
            return $this->error(trans('common.again'));
        }
        
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


            $currencyPO=Currency::getNameById($currency_id);

            //$currencyName=$currencyPO->name;
            
             $currencyName=$request->input('currencyName','');
            
            //throw new \Exception("===>>".$currencyName);




            // $currencyName=strtolower($currencyName);
            // $url="https://api.huobi.pro/market/detail/merged?symbol=".$currencyName."usdt";
            // $result= $this->curl($url);
            // $result=json_decode($result);
            // $tick=$result->tick;
            // $price=$tick->close;
            
            // 直接从数据库里拿
            $result = CurrencyQuotation::where(['currency_id' => $currency_id,'legal_id' => $legal_id])->first();
            $price = $result->close;


//            if (mt_rand(0, 1)) {   2023-05-19注释掉
//                $price = bc_add($price, $float_diff);
//            } else {
//                $price = bc_sub($price, $float_diff);
//            }
            $amount=bc_div($number,$price);
            $order_data = [
                  'u' =>$number,
                'user_id' => $user_id,
                'type' => $type,
                 'match_id' => $match_id,
                'currency_id' => $currency_id,
                'seconds' => $seconds,
                'price' => $price,
                'number' => $amount,
                'use_insurance' => $use_insurance,
            ];
            $order = MicroTradeLogic::addOrder($order_data);
            Redis::set('micro_tip_count', 1);
            // 机器人推送消息
            robotSendMessage($user_id,'期权交易'.$seconds.'S 金额:'.$number);
            return $this->success($order);
        } catch (\Throwable $th) {
            //return $this->error('File:' . $th->getFile() . ',Line:' . $th->getLine() . ',Message:' . $th->getMessage());

            return $this->error($th->getMessage());
            //return $this->error($th->getMessage());
        }
    }






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
    public function listsV2(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
       try {
            $user_id = Users::getUserId();
            $limit = $request->input('limit', 10);
            $status = $request->input('status', -1);
            $match_id = $request->input('match_id', -1);
            $currency_id = $request->input('currency_id', -1);
            $currencyName=Currency::getNameById($currency_id);
            
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
            $jo["list"]=$lists;
            $jo["currencyName"]=$currencyName;

            return $this->success($jo);
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
