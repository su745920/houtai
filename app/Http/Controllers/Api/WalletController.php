<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\Models\{Currency,
    Address,
    AccountLog,
    Setting,
    Users,
    UsersWallet,
    UsersWalletOut,
    LeverTransaction,
    CurrencyQuotation,
    LoanOrder
};
use App\Events\WithdrawSubmitEvent;
use App;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Console\Input\Input;
use Earnp\GoogleAuthenticator\GoogleAuthenticator;

class WalletController extends Controller
{
    public function getBalanceByCoin(Request $request){
        $coin=$request->input("coin");
        $user_id = Users::getUserId();
        $user_wallet = db::table('users_wallet')->where('currency',$coin)->where('user_id',$user_id)->first();
        return $this->success($user_wallet);
    }

    public function hzhistory(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 10);

        $arr = [
            AccountLog::WALLET_LEGAL_OUT,
            AccountLog::WALLET_LEVER_OUT,
            AccountLog::WALLET_MCIRO_OUT,
            AccountLog::WALLET_CHANGE_OUT,
            AccountLog::WALLET_LEGAL_IN,
            AccountLog::WALLET_LEVER_IN,
            AccountLog::WALLET_MCIRO_IN,
            AccountLog::WALLET_CHANGE_IN,
            AccountLog::WALLET_EARN_OUT,
            AccountLog::WALLET_EARN_IN
        ];
        $result = AccountLog::where('user_id', $user_id)->whereIn('type', $arr)->orderBy('id', 'desc')->paginate($limit);
        return $this->success($result);

    }

    private $fromArr = [
        'legal' => AccountLog::WALLET_LEGAL_OUT,
        'lever' => AccountLog::WALLET_LEVER_OUT,
        'micro' => AccountLog::WALLET_MCIRO_OUT,
        'change' => AccountLog::WALLET_CHANGE_OUT,
        'earn' => AccountLog::WALLET_EARN_OUT
    ];
    private $toArr = [
        'legal' => AccountLog::WALLET_LEGAL_IN,
        'lever' => AccountLog::WALLET_LEVER_IN,
        'micro' => AccountLog::WALLET_MCIRO_IN,
        'change' => AccountLog::WALLET_CHANGE_IN,
        'earn' => AccountLog::WALLET_EARN_IN
    ];

        // 'legal' => 'funding',
        // 'lever' => 'Contracts',
        // 'micro' => 'Option',
        // 'change' => 'Exchange',
        // 'earn' => 'Minging'
    private $mome = [
        'legal' => '资金',
        'lever' => '合约',
        'micro' => '秒合约',
        'change' => '现货',
        'earn' => '理财'
    ];
    private $enmome = [
        'legal' => 'Fund',
        'lever' => 'Contract',
        'micro' => 'Second contract',
        'change' => 'Spots',
        'earn' => 'Financing'
    ];
    
    
   public function getLegalAssets(Request $request)
    {
        $lang = request()->input('lang', 'en');
        $currency_name="";
        $user_id=Users::getUserId();
        $user_wallet = UsersWallet::with(['currencyCoin'])->where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                $query->where('is_legal',1);

            })->get();
        $user_wallet->transform(function ($item, $key) {
            $item->setVisible([
                'logo',
                'id', 'currency', 'currency_name', 'sort',
                'currency_type', 'contract_address',
                'usdt_price', 'usd_price', 'multi_protocol',
                'legal_balance', 'lock_legal_balance', 'is_recharge', 'is_withdraw',
                'lever_balance', 'lock_lever_balance',
                'change_balance', 'lock_change_balance',
                'micro_balance', 'lock_micro_balance',
                'earn_balance', 'lock_earn_balance',
                'address', 'erc20_address',
                'is_legal', 'is_lever', 'is_match', 'is_transfer', 'is_micro', 'is_transfer', 'is_distransfer',
            ]);
            return $item;
        });
        if ($lang == "vi") {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [63, 82, 84])->sortByDesc('legal_balance')->values()->all();
        } elseif ($lang == "th") {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [63, 81, 84])->sortByDesc('legal_balance')->values()->all();
        } elseif ($lang == "id") {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [63, 81, 82])->sortByDesc('legal_balance')->values()->all();
        } elseif ($lang == "zh") {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [81, 82, 84])->sortByDesc('legal_balance')->values()->all();
        } else {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [63, 81, 82, 84])->sortByDesc('legal_balance')->values()->all();
        }

        $legal_wallet['totle'] = 0;

        foreach ($legal_wallet['balance'] as $k => $v) {

            $num = $v['legal_balance'];
            $currency = $v['currency'];
            if ($v["id"] == 23) {
                $legal_wallet['totle'] += $num;

            } else {
                //$settingVO
                if ($lang == "vi") {
                    if ($currency == 81) {

                        $legal_wallet['totle'] += $num;

                    } else {
                        $settingVO = Setting::getValueByKey('vnd');
                        $legal_wallet['totle'] += $num * $v['usdt_price']*$settingVO;

                    }
                } else if ($lang == "th") {
                    $settingVO = Setting::getValueByKey('thb');
                    if ($currency == 82) {

                        $legal_wallet['totle'] += $num;

                    } else {
                        $legal_wallet['totle'] += $num * $v['usdt_price']*$settingVO;

                    }

                } else if ($lang == "id") {
                    if ($currency == 83) {

                        $legal_wallet['totle'] += $num;

                    } else {
                        $settingVO = Setting::getValueByKey('idr');
                        $legal_wallet['totle'] += $num * $v['usdt_price']*$settingVO;

                    }
                } else {
                    if ($currency == 63) {

                        $legal_wallet['totle'] += $num;

                    } else {
                        $settingVO = Setting::getValueByKey('rmb');
                        $legal_wallet['totle'] += $num * $v['usdt_price']*$settingVO;

                    }
                }



            }
        }
        $jo["total_legal"]=$legal_wallet['totle'];
        return $this->success($jo);

    }

    
    

    public function changeWallet2(Request $request)  //BY tian
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        // $type = [
        //     'legal' => 1,
        //     'lever' => 3,
        //     'micro' => 4,
        //     'change' => 2,
        // ];
        $type = [
            'legal' => 0,
            'change' => 1,
            'lever' => 2,
            'micro' => 3,
            'earn' => 4,
        ];


        $user_id = Users::getUserId();
        $currency_id = request()->get("currency_id", '');
        $number = request()->get("number", '');

        $user = Users::find($user_id);
        if ($user->frozen_funds == 1) {
            return $this->error('Funds are frozen');//资金已冻结
        }
        $from_field = $request->get('from_field', "");
        $to_field = $request->get('to_field', "");
        if (empty($from_field) || empty($number) || empty($to_field) || empty($currency_id)) {
            return $this->error('参数错误');
        }
        if ($number < 0) {
            return $this->error('输入的金额不能为负数');
        }
        $from_account_log_type = $this->fromArr[$from_field];
        $to_account_log_type = $this->toArr[$to_field];
        $memo = $this->mome[$from_field] . '划转' . $this->mome[$to_field];
        $enmemo = $this->enmome[$from_field] . ' transfer ' . $this->enmome[$to_field];
        if ($from_field == 'lever') {
            if ($this->hasLeverTrade($user_id)) {
                return $this->error('您有正在进行中的杆杠交易,不能进行此操作');
            }
        }
        try {
            DB::beginTransaction();
            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency_id)
                ->first();
            if (!$user_wallet) {
                throw new \Exception('钱包不存在');
            }
            $result = change_wallet_balance($user_wallet, $type[$from_field], -$number, $from_account_log_type, $memo,$enmemo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            $result = change_wallet_balance($user_wallet, $type[$to_field], $number, $to_account_log_type, $memo,$enmemo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('划转成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('操作失败:' . $e->getMessage());
        }
    }

    public function withdrawLogList(Request $request)
    {

        $user_id = Users::getUserId();
        $list = UsersWalletOut::where('user_id', $user_id)->get()->toArray();
        // foreach ($list as $po){
        //     $currency=$po->currency;
        //     $currencyName=Currency::getNameById($currency);
        //     $po->currencyName=$currencyName;
        // }
        return $this->success($list);

    }
    public function withdrawLogDetail(Request $request) {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $id = $request->input('id');
        $data = UsersWalletOut::where('user_id',$user_id)->where('id',$id)->first();
        if(empty($data)) {
            return $this->error('Error'); 
        }
        return $this->success($data);
    }


    public function walletList(Request $request)
    {

        $lang = request()->input('lang','en');
        $currency_name = $request->input('currency_name', '');
        $user_id = Users::getUserId();
        
         $currency_id = 63;
        $settingVO=7.19;


        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }

        $USDTRate = Setting::getValueByKey('USDTRate', 7.22);

        $cache_key_name = "user_wallet_data_{$user_id}";
        // if (Cache::has($cache_key_name)) {
        //     $wallet_data = Cache::get($cache_key_name);
        // } else {
        $user_wallet = UsersWallet::with(['currencyCoin'])->where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {

                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');

            })->get();

        $user_wallet->transform(function ($item, $key) {
            $item->setVisible([
                'logo',
                'id', 'currency', 'currency_name', 'sort',
                'currency_type', 'contract_address',
                'usdt_price', 'usd_price', 'multi_protocol',
                'legal_balance', 'lock_legal_balance', 'is_recharge', 'is_withdraw',
                'lever_balance', 'lock_lever_balance',
                'change_balance', 'lock_change_balance',
                'micro_balance', 'lock_micro_balance',
                'earn_balance', 'lock_earn_balance',
                'address', 'erc20_address',
                'is_legal', 'is_lever', 'is_match', 'is_transfer', 'is_micro', 'is_transfer', 'is_distransfer',
            ]);
            return $item;
        });
        
        //$legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->values()->all();
        
        //$legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->sortByDesc('legal_balance')->values()->all();
        if ($lang=="vi") {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency',[63,82,84])->sortByDesc('legal_balance')->values()->all();
        }elseif ($lang=="th"){
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency',[63,81,84])->sortByDesc('legal_balance')->values()->all();
        }elseif ($lang=="id"){
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency',[63,81,82])->sortByDesc('legal_balance')->values()->all();
        }elseif ($lang=="zh"){
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency',[81,82,84])->sortByDesc('legal_balance')->values()->all();
        }else{
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency',[63,81,82,84])->sortByDesc('legal_balance')->values()->all();
        }
        
        
        
        $legal_wallet['totle'] = 0;
        $legal_wallet['CNY'] = 0;
        foreach ($legal_wallet['balance'] as $k => $v) {
            //$num = $v['legal_balance'] + $v['lock_legal_balance'];
            $num = $v['legal_balance'];
            $currency=$v['currency'];
            if ($v["currency"] == 23) {
                $legal_wallet['totle'] += $num;
                $legal_wallet['CNY'] += $num;
            } else {
                // $legal_wallet['totle'] += $num * $v['usdt_price'];
                // $legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
                
                //$settingVO
                if ($lang=="vi"){
                    if($currency==81){
                        $settingVO=Setting::getValueByKey('vnd');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    }else{
                        $legal_wallet['totle'] += $num * $v['usdt_price'];
                        $legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
                    }


                } else if ($lang=="th"){
                    $settingVO=Setting::getValueByKey('thb');
                    if($currency==82){
                        $settingVO=Setting::getValueByKey('vnd');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    }else{
                        $legal_wallet['totle'] += $num * $v['usdt_price'];
                        $legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
                    }

                }else if($lang=="id"){

                    if($currency==83){
                        $settingVO=Setting::getValueByKey('idr');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    }else{
                        $legal_wallet['totle'] += $num * $v['usdt_price'];
                        $legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
                    }
                }else{

                   if ($currency == 100) {
                        $settingVO = Setting::getValueByKey('usd');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    }
                    else if ($currency == 101) {
                        $settingVO = Setting::getValueByKey('eur');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    }
                    else if ($currency == 102) {
                        $settingVO = Setting::getValueByKey('gbp');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    }
                    else if ($currency == 63) {
                        $settingVO = Setting::getValueByKey('rmb');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    } else {
                        $legal_wallet['totle'] += $num * $v['usdt_price'];
                        $legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
                    }
                }
                ///
                
            }
        }

        //221120 币币资产只显示余额不为0的资产（含冻结）
        //$change_list = $user_wallet->where('is_match', 1)->sortBy("sort")->values()->all();
        $change_list = $user_wallet->where('is_match', 1)->sortByDesc("change_balance")->values()->all();

        $change_wallet['balance'] = $change_list;
        $change_wallet['totle'] = 0;
        $change_wallet['CNY'] = 0;
        foreach ($change_wallet['balance'] as $k => $v) {
            //$num = $v['change_balance'] + $v['lock_change_balance'];
            $num= $v['change_balance'];
            if ($v["currency"] == 23) {
                $change_wallet['totle'] += $num;
                $change_wallet['CNY'] += $num;
            } else {
                $change_wallet['totle'] += $num * $v['usdt_price'];
                $legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
            }
            // 20221126 小数点
            if (in_array($v['currency'], [32, 35])) {
                $change_wallet['balance'][$k]['change_balance'] = number_format($v['change_balance'], 8, '.', '');
                $change_wallet['balance'][$k]['lock_change_balance'] = number_format($v['lock_change_balance'], 8, '.', '');
            } else {
                $change_wallet['balance'][$k]['change_balance'] = number_format($v['change_balance'], 4, '.', '');
                $change_wallet['balance'][$k]['lock_change_balance'] = number_format($v['lock_change_balance'], 4, '.', '');
            }
        }

        //221120 账户资产只显示 USDT，BTC，ETH，USDC，USD，CNY
        //$lever_list = $user_wallet->where('is_lever', 1)->whereIn('currency', [23, 32, 35, 57, 58, 63])->values()->all();
        
        $lever_list = $user_wallet->where('is_lever', 1)->whereIn('currency', [23, 32, 35, 57, 58, 63])->sortByDesc("lever_balance")->values()->all();

        $lever_wallet['balance'] = $lever_list;
        $lever_wallet['totle'] = 0;
        $lever_wallet['CNY'] = 0;

        foreach ($lever_wallet['balance'] as $k => $v) {
            //$num = bc_add($v['lever_balance'], $v['lock_lever_balance'], 6);
            $num=$v['lever_balance'];
            if ($v["currency"] == 23) {
                $lever_wallet['totle'] += $num;
                $lever_wallet['CNY'] += $num;

            } else {
                $lever_wallet['totle'] += bc_mul($num, $v['usdt_price'], 6);
                $lever_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
            }

            // 20221126 小数点
            if (in_array($v['currency'], [32, 35])) {
                $lever_wallet['balance'][$k]['lever_balance'] = number_format($v['lever_balance'], 8, '.', '');
                $lever_wallet['balance'][$k]['lock_lever_balance'] = number_format($v['lock_lever_balance'], 8, '.', '');
            } else {
                $lever_wallet['balance'][$k]['lever_balance'] = number_format($v['lever_balance'], 4, '.', '');
                $lever_wallet['balance'][$k]['lock_lever_balance'] = number_format($v['lock_lever_balance'], 4, '.', '');
            }

        }
        //$micro_list = $user_wallet->where('is_micro', 1)->values()->all();
        $micro_list = $user_wallet->where('is_micro', 1)->sortByDesc("micro_balance")->values()->all();

        //秒合约账户
        $micro_wallet['CNY'] = 0;
        $micro_wallet['totle'] = 0;
        $micro_wallet['balance'] = $micro_list;

        foreach ($micro_wallet['balance'] as $k => $v) {
            //$num = bc_add($v['micro_balance'], $v['lock_micro_balance'], 6);
            $num=$v['micro_balance'];
            if ($v["currency"] == 23) {
                $micro_wallet['totle'] += $num;
                $micro_wallet['CNY'] += bc_mul($num, $USDTRate, 6);
            } else {
                $micro_wallet['totle'] += bc_mul($num, $v['usdt_price'], 6);
                $micro_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
            }

        }

        //理财账户
        //$earn_list = $user_wallet->where('is_earn', 1)->values()->all();
        //$earn_list = $user_wallet->values()->all();
        $earn_list = $user_wallet->sortByDesc("earn_balance")->values()->all();
        $earn_wallet['CNY'] = 0;
        $earn_wallet['totle'] = 0;
        $earn_wallet['balance'] = $earn_list;
        foreach ($earn_wallet['balance'] as $k => $v) {

            //$num = bc_add($v['earn_balance'], $v['lock_earn_balance'], 6);
            $num=$v['earn_balance'];
            if ($v["currency"] == 23) {
                $earn_wallet['totle'] += $num;
                $earn_wallet['CNY'] += bc_mul($num, $USDTRate, 6);
            } else {
                $earn_wallet['totle'] += bc_mul($num, $v['usdt_price'], 6);
                $earn_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
            }
        }
        //


        //读取是否开启充提币
        $is_open_ctbi = Setting::getValueByKey("is_open_CTbi");
        $btc=Redis::get('btc');
        $wallet_data = [
            'earn_wallet' =>$earn_wallet,
            'legal_wallet' => $legal_wallet,
            'change_wallet' => $change_wallet,
            'lever_wallet' => $lever_wallet,
            'micro_wallet' => $micro_wallet,
            "is_open_ctbi" => $is_open_ctbi,
            'is_open_CTbi' => $is_open_ctbi,
            'ExRate' => $USDTRate,
            'USDTRate' => $USDTRate,
            'btc'=> $btc
        ];
        //Cache::put($cache_key_name, $wallet_data, 60);
        //  }
        return $this->success($wallet_data);
    }

    //币种列表
    public function currencyList()
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $currency = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        if (empty($currency)) {
            return $this->error(trans('wallet.zshmytjbz'));
        }
        foreach ($currency as $k => $c) {
            $w = Address::where("user_id", $user_id)->where("currency", $c['id'])->count();
            $currency[$k]['has_address_num'] = $w; //已添加提币地址数量
        }
        return $this->success($currency);
    }

    //添加提币地址
    public function addAddress()
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $user_id = Users::getUserId();
        $id = request()->input("currency_id", '');
        $address = request()->input("address", "");
        $notes = request()->input("notes", "");
        if (empty($user_id) || empty($id) || empty($address)) {
            return $this->error(trans('wallet.cscw'));
        }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error(trans('wallet.yhwzd'));
        }
        $currency = Currency::find($id);
        if (empty($currency)) {
            return $this->error(trans('wallet.cbzbcz'));
        }
        $has = Address::where("user_id", $user_id)->where("currency", $id)->where('address', $address)->first();
        if ($has) {
            return $this->error(trans('wallet.yjyctbdz'));
        }
        try {

            $currency_address = new Address();
            $currency_address->address = $address;
            $currency_address->notes = $notes;
            $currency_address->user_id = $user_id;
            $currency_address->currency = $id;
            $currency_address->save();
            return $this->success(trans('wallet.tjtbdzcg'));
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    //删除提币地址
    public function addressDel()
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $user_id = Users::getUserId();
        $address_id = request()->input("address_id", '');

        if (empty($user_id) || empty($address_id)) {
            return $this->error(trans('wallet.cscw'));
        }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error(trans('wallet.yhwzd'));
        }
        $address = Address::find($address_id);

        if (empty($address)) {
            return $this->error(trans('wallet.ctbdzbcz'));
        }
        if ($address->user_id != $user_id) {
            return $this->error(trans('wallet.nmyqxsccdz'));
        }

        try {
            $address->delete();
            return $this->success(trans('wallet.sctbdzcg'));
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    /**
     *法币账户划转到交易账户
     *划转 法币账户只能划转到交易账户  杠杆账户只能和交易账户划转
     *划转类型type 1 法币(c2c)划给杠杆币 2 杠杆划给法币 3法币划给交易币 4交易币划给法币
     *记录日志
     */
    public function changeWallet()  //BY tian
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $currency_id = request()->input("currency_id", '');
        $number = request()->input("number", '');
        $type = request()->input("type", ''); //1从法币划到交易账号
        if (empty($currency_id) || empty($number) || empty($type)) {
            return $this->error(trans('wallet.cscw'));
        }
        if ($number < 0) {
            return $this->error(trans('wallet.srjebnwfs'));
        }

        switch ($type) {
            case 1:
                $from_field = 1;
                $to_field = 3;
                $from_account_log_type = AccountLog::WALLET_LEGAL_OUT;
                $to_account_log_type = AccountLog::WALLET_LEVER_IN;
                $memo = '法币划转杠杆币';
                $enmemo = 'Legal currency transfer lever currency';
                break;
            case 2:
                $from_field = 3;
                $to_field = 1;
                $from_account_log_type = AccountLog::WALLET_LEVER_OUT;
                $to_account_log_type = AccountLog::WALLET_LEGAL_IN;
                $memo = '杠杆币划转法币';
                $enmemo = 'Transfer of leveraged currency to legal currency';
                if ($this->hasLeverTrade($user_id)) {
                    return $this->error('您有正在进行中的杆杠交易,不能进行此操作');
                }
                break;
            case 3:
                $from_field = 1;
                $to_field = 2;
                $from_account_log_type = AccountLog::WALLET_LEGAL_OUT;
                $to_account_log_type = AccountLog::WALLET_CHANGE_IN;
                $memo = '法币划转交易币';
                $enmemo = 'Legal currency transfer transaction currency';
                break;
            case 4:
                $from_field = 2;
                $to_field = 1;
                $from_account_log_type = AccountLog::WALLET_CHANGE_OUT;
                $to_account_log_type = AccountLog::WALLET_LEGAL_IN;
                $memo = '交易币划转法币';
                $enmemo = 'Transfer of transaction currency to legal currency';

                break;
            default:
                return $this->error('划转类型错误');
                break;
        }
        try {
            DB::beginTransaction();
            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency_id)
                ->first();
            if (!$user_wallet) {
                throw new \Exception(trans('wallet.cbbcz'));
            }
            $result = change_wallet_balance($user_wallet, $from_field, -$number, $from_account_log_type, $memo, $enmemo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            $result = change_wallet_balance($user_wallet, $to_field, $number, $to_account_log_type, $memo, $enmemo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            //增加 法币与杠杆的互转记录
            if ($type == 1 || $type == 2) {
                // $res11 = new Levertolegal();
                // $res11->user_id = $user_id;
                // $res11->number = $number;
                // $res11->type = $type;
                // $res11->status = 2; //2：审核通过
                // $res11->add_time = time();
                // $res11->save();
            }
            DB::commit();
            return $this->success(trans('wallet.hzcg'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(trans('wallet.czsb') . $e->getMessage());
        }
    }

    /**
     * 同账户钱包内划转
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountTransfer(Request $request)
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $wallet_id = $request->input('wallet_id', 0);
        $from = $request->input('from', '');
        $to = $request->input('to', '');
        $number = $request->input('number', 0);
        $user_id = Users::getUserId();
        $key = 'lan_type_' . $user_id;
        $lang = Cache::get($key);

        $balance_types = [];
        if ($lang == 'zh_cn') {
            $balance_types = [
                'legal' => [1, '法币账户'],
                'change' => [2, '币币账户'],
                'lever' => [3, '杠杆账户'],
                'micro' => [4, '秒合约账户'],
            ];
        } elseif ($lang == 'en') {
            $balance_types = [
                'legal' => [1, 'Legal currency account'],
                'change' => [2, 'Currency account'],
                'lever' => [3, 'Leveraged account'],
                'micro' => [4, 'micro account'],
            ];
        } elseif ($lang == 'de') {
            $balance_types = [
                'legal' => [1, 'Rechtliches Währungskonto'],
                'change' => [2, 'Konto in Währung'],
                'lever' => [3, 'Konto Leveraged'],
                'micro' => [4, 'Zweites Vertragskonto'],
            ];
        } elseif ($lang == 'es') {
            $balance_types = [
                'legal' => [1, ' Cuenta en moneda francesa'],
                'change' => [2, 'Cuenta de moneda'],
                'lever' => [3, 'Cuenta apalancada'],
                'micro' => [4, 'Cuenta de segundo contrato'],
            ];
        } elseif ($lang == 'fr') {
            $balance_types = [
                'legal' => [1, 'Comptes en monnaie française'],
                'change' => [2, 'Compte en monnaie'],
                'lever' => [3, 'Compte de levier'],
                'micro' => [4, 'Deuxième compte contractuel'],
            ];
        } elseif ($lang == 'hk') {
            $balance_types = [
                'legal' => [1, '法幣帳戶'],
                'change' => [2, '幣幣帳戶'],
                'lever' => [3, '杠杆帳戶'],
                'micro' => [4, '秒合約帳戶'],
            ];
        } elseif ($lang == 'ita') {
            $balance_types = [
                'legal' => [1, 'Conto di valuta legale'],
                'change' => [2, 'Conto di valuta'],
                'lever' => [3, 'Conto leveraged'],
                'micro' => [4, 'Secondo conto contrattuale'],
            ];
        } elseif ($lang == 'jp') {
            $balance_types = [
                'legal' => [1, '仏貨の口座'],
                'change' => [2, '貨幣の口座'],
                'lever' => [3, 'てこの口座'],
                'micro' => [4, '秒契約アカウント'],
            ];
        } elseif ($lang == 'kr') {
            $balance_types = [
                'legal' => [1, '법정 화폐 계좌'],
                'change' => [2, '화폐 계좌'],
                'lever' => [3, '레버 리 지 계 정'],
                'micro' => [4, '초 계약 계좌'],
            ];
        } else {
            $balance_types = [
                'legal' => [1, 'Legal currency account'],
                'change' => [2, 'Currency account'],
                'lever' => [3, 'Leveraged account'],
                'micro' => [4, 'micro account'],
            ];
        }
        $keys = array_keys($balance_types);
        $keys = [
            '0' => 'legal',
            '1' => 'change',
            '2' => 'lever',
            '3' => 'micro'

        ];
        $values = array_values($balance_types);
        $balance_name = array_column($values, 1);


        $balance_types_en = [
            'legal' => [1, 'Legal currency account'],
            'change' => [2, 'Currency account'],
            'lever' => [3, 'Leveraged account'],
            'micro' => [4, 'micro account'],
        ];
        try {
            DB::beginTransaction();
            if ($from == '' || $to == '') {
                throw new \Exception(trans('wallet.hzzhlxbxxz'));
            }
            if ($from == $to) {
                throw new \Exception(trans('wallet.hzzhlxbnxt'));
            }

            if (!in_array($from, $keys) || !in_array($to, $keys)) {
                throw new \Exception(trans('wallet.hzzhlxbgf') . implode('、', $balance_name));
            }
            if (bc_comp_zero($number) <= 0) {
                throw new \Exception(trans('wallet.hzslbxdy'));
            }
            $wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->findOrFail($wallet_id);
            // 判断划出余额是否充足
            if (bc_comp($wallet->{$from . '_balance'}, $number) < 0) {
                //throw new \Exception(end($balance_types[$from]) . trans('wallet.kczyebz'));
                throw new \Exception(end($balance_types[$from]) . trans('wallet.kczyebz'));
            }
            $extra_data = [
                'from' => $from,
                'to' => $to,
            ];
            $enmemo = 0;
            if ($from == 'change') {
                $enmemo = 8;
            } elseif ($from == 'lever') {
                $enmemo = 22;
            } elseif ($from == 'micro') {
                $enmemo = 32;
            }
            change_wallet_balance(
                $wallet,
                reset($balance_types[$from]),
                -$number,
                AccountLog::WALLET_ACCOUNT_TRANSFER_OUT,
                end($balance_types[$from]) . '划出',
                end($balance_types[$from]) . ' draw out',
                false,
                0,
                0,
                serialize($extra_data)
            );

            if ($to == 'change') {
                $enmemo = 7;
            } elseif ($to == 'lever') {
                $enmemo = 21;
            } elseif ($to == 'micro') {
                $enmemo = 31;
            }
            change_wallet_balance(
                $wallet,
                reset($balance_types[$to]),
                $number,
                AccountLog::WALLET_ACCOUNT_TRANSFER_IN,
                end($balance_types[$to]) . '划入',
                end($balance_types[$to]) . ' transfer in',
                false,
                0,
                0,
                serialize($extra_data)
            );
            DB::commit();
            return $this->success(trans('wallet.hzcg'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error(trans('wallet.czsb') . $th->getMessage());
        }
    }

    public function hasLeverTrade($user_id)
    {
        $exist_close_trade = LeverTransaction::where('user_id', $user_id)
            ->whereNotIn('status', [LeverTransaction::CLOSED, LeverTransaction::CANCEL])
            ->count();
        return $exist_close_trade > 0 ? true : false;
    }

//    public function hzhistory()
//    {
//        //         $user_id = Users::getUserId();
//        //         $result = new Levertolegal();
//        //         $count = $result::all()->count();
//        //         $result = $result->orderBy("add_time", "desc")->where("user_id", "=", $user_id)->get()->toArray();
//        //         foreach ($result as $key => $value) {
//        //             $result[$key]["add_time"] = date("Y-m-d H:i:s", $value["add_time"]);
//        //             if ($value["type"] == 1) {
//        //                 $result[$key]["type"] = "杠杆转法币";
//        //             } elseif ($value["type"] == 2) {
//        //                 $result[$key]["type"] = "法币转杠杆";
//        //             }
//
//        //         }
//        // //        var_dump($result);die;
//
//        //         return response()->json(['type' => "ok", 'data' => $result, 'count' => $count]);
//    }

    public function getCurrency()
    {
        $list = Currency::where('is_recharge', 1)->select(['id', 'name'])->get();
        foreach ($list as &$k) {
            if ($k['id'] != 23) {
                $k['twd'] = (CurrencyQuotation::where('currency_id', $k['id'])->value('now_price')) * Setting::getValueByKey('USDTRate');
            } else {
                $k['twd'] = Setting::getValueByKey('USDTRate');
            }
        }

        return $this->success($list);
    }

    public function getRecharge()
    {
        $data = [
            'bank' => Setting::getValueByKey('bank_name', ''),
            'real_name' => Setting::getValueByKey('bank_realname'),
            'card' => Setting::getValueByKey('bank_card_number')
        ];

    }

    /**
     * 获取币种相关信息
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function getCurrencyInfo()
    {
        $user_id = Users::getUserId();
        $currency_id = request()->input("currency", '');

        try {
            throw_if(empty($currency_id), new \Exception(trans('wallet.cscw')));
            $user = Users::findOrFail($user_id);
            $currency_info = Currency::findOrFail($currency_id);
            //多协议  
            $data = [];
            $type_data = [];
            $wallet_data = [];
            $wallet = UsersWallet::where('user_id', $user_id)
                ->where('currency', $currency_id)
                ->firstOrFail();
             
            // 注释掉全局钱包地址覆盖机制，避免安全风险
            /*
            if(!empty($wallet))
            {
                $address = '';
                $erc20_address ='';
                if($wallet['currency'] == 23){
                    $address = Setting::getValueByKey('USDTAddress', '');
                    $erc20_address = Setting::getValueByKey('USDTAddress_erc', '');
                }
                if($wallet['currency'] == 32){
                    $address = Setting::getValueByKey('BTCAddress', '');
                }
                if($wallet['currency'] == 35){
                    $address = Setting::getValueByKey('ETHAddress', '');
                }
                $wallet->address = $address;
                $wallet->erc20_address = $erc20_address;
                $wallet->save();
                $wallet->refresh();
            }
            */
// ... existing code ...

            $user_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first();

            // 注释掉全局钱包地址覆盖机制，避免安全风险
            /*
            if(empty($user_wallet['address']) || empty($user_wallet['erc20_address'])){
                $is_open_CTbi = Setting::getValueByKey('is_open_CTbi', '');
                $currency = Currency::where('id',$currency_id)->first();
                if($is_open_CTbi == '1' && $currency['name'] == 'USDT'){
                    $user_wallet->address = Setting::getValueByKey('USDTAddress', '');
                    $user_wallet->erc20_address = Setting::getValueByKey('USDTAddress_erc', '');
                }
                if($is_open_CTbi == '1' && $currency['name'] == 'BTC'){
                    $user_wallet->address = Setting::getValueByKey('BTCAddress', '');
                }
                if($is_open_CTbi == '1' && $currency['name'] == 'ETH'){
                    $user_wallet->address = Setting::getValueByKey('ETHAddress', '');
                }
                $user_wallet->save(); //默认生成所有币种的钱包
            }
            */
// ... existing code ...
            if ($currency_info->multi_protocol == 1) {
                $type_data = Currency::where('parent_id', $currency_id)->get()->toArray();
                foreach ($type_data as $v) {
                    $son_wallet = UsersWallet::where('user_id', $user_id)
                        ->where('currency', $v['id'])
                        ->first();
                    if ($son_wallet && $son_wallet->address) {
                        $wallet_data[] = $son_wallet;
                    }
                }
            } else {
                $wallet_data[] = $wallet;
            }
            
            $withdraw_fee=Setting::getValueByKey("withdraw_fee");

            $data = [
                'name' => $currency_info->name,
                'type' => $currency_info->type,
                'multi_protocol' => $currency_info->multi_protocol, // 是否支持多协议
                'make_wallet' => $currency_info->make_wallet, // 生成用户钱包的策略:0.不生成,1.接口生成,2.从总钱包继承,3.空钱包
                'rate' => $currency_info->rate,
                'withdraw_fee' =>$withdraw_fee,
                'min_number' => $currency_info->min_number,
                'contract_address' => $currency_info->contract_address,
                'change_balance' => $wallet->change_balance ?? 0,
                'legal_balance' => $wallet->legal_balance ?? 0,
                'lever_balance' => $wallet->lever_balance ?? 0,
                'type_data' => $type_data,
                'wallet_data' => $wallet_data,
                'label' => $user->extension_code,
                'bank' => Setting::getValueByKey('bank_name', ''),
                'real_name' => Setting::getValueByKey('bank_realname'),
                'card' => Setting::getValueByKey('bank_card_number'),
                'pay_password' => $user->pay_password
            ];
            return $this->success($data);

        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    //提币地址，根据currency_id列表地址,提币的时候需要选择地址
    public function getAddressByCurrency()
    {
        $user_id = Users::getUserId();
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $currency_id = request()->input("currency", '');
        if (empty($user_id) || empty($currency_id)) {
            return $this->error(trans('wallet.cscw'));
        }

        $address = Address::where('user_id', $user_id)->where('currency', $currency_id)->get()->toArray();

        if (empty($address)) {
            //return $this->error(trans('wallet.nhmytjtbdz'));
            //return $this->success(trans('wallet.nhmytjtbdz'));
        }
        return $this->success($address);
    }

    //提币地址
    public function getAddress()
    {
        $user_id = Users::getUserId();
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }
        $address = Address::where('user_id', $user_id)->get()->toArray();
        if (empty($address)) {
            return $this->error(trans('wallet.nhmytjtbdz'));
        }
        return $this->success($address);
    }

    //提交提币信息。数量。
    public function postWalletOut(Request $request)
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $currency_id = request()->input("currency", 0);
        $number = $request->input("number", 0);
        $address = $request->input("address", '') ?? '';
        $code = $request->input('code', '') ?? '';
        $memo = $request->input("memo", '') ?? '';
        $type = $request->input("type", '') ?? ''; //协议类型
        $label = $request->input("label", '') ?? ''; //标签
        $payPassword = $request->input("payPassword", '') ?? ''; //提现密码
        $usdt_type = $request->input("usdtType",'') ?? '';
        $googleCode = $request->input('googleCode', '') ?? '';
        
        // 判断是否开启了实名认证校验
        if(Setting::getValueByKey("is_open_transaction",0) == 1) {
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
        

        try {
            DB::beginTransaction();
            if ($currency_id==63||$currency_id==81||$currency_id==82||$currency_id==84){
                if (empty($currency_id) || empty($currency_id)) {
                    throw new \Exception(trans('wallet.cscw')."--1");
                }
            }else {
                if (empty($currency_id) || empty($currency_id) || empty($address)) {
                    throw new \Exception(trans('wallet.cscw'));
                }
            }
            $user = Users::findOrFail($user_id);
            $withdraw_must_code = Setting::getValueByKey('withdraw_must_code', 0);
            if ($code == '' && $withdraw_must_code) {
                throw new \Exception(trans('wallet.yzmbxtx'));
            }
            // if ($withdraw_must_code && $code != session('code@' . $user->country_code . $user->account_number)) {
            //     throw new \Exception(trans('wallet.yzmcw'));
            // }
            // var_dump(Cache::get('code@' . $user->email));
             if($code !== Cache::get('code@' . $user->email)) {
                return $this->error(trans('wallet.yzmcw'));
            }
            // 验证谷歌验证码
            if($user->google_secret) {
                // if (!GoogleAuthenticator::CheckCode($user->google_secret, $googleCode)){
                //     return $this->error(trans('blind.ggyzmcw'));
                // }
            }
            
            // 判断是否存在贷款未还
            $loanOrder = LoanOrder::where(['user_id' => $user_id])->whereIn('status',[1,3,4,5])->count();
            if($loanOrder > 0) {
                 return $this->error(trans('wallet.dqywchddk'));
            }
            
            //20221120 验证手势密码
            // if ($user->pay_password != Users::MakePassword($payPassword)) {
            //     throw new \Exception(trans('user.mmcw'));
            // }
            
            // 验证信用分
            $credit_score = Setting::getValueByKey('credit_score',0);//获取提现最低信用分
            if ($user->credit_score < $credit_score) {
                throw new \Exception(trans('user.xyf_start').$credit_score.trans('user.xyf_end'));
            }
            $currency = Currency::findOrFail($currency_id);
            if ($currency->multi_protocol == 1) {
                if ($type == '') {
                    throw new \Exception(trans('wallet.qxzxylx'));
                }
                $currency = Currency::where('parent_id', $currency_id)->where('multi_protocol', 0)->where('type', $type)->firstOrFail();
            } else {
                $type = $currency->type;
            }
            //标签
            if ($currency->make_wallet == 2 && $label == '' && $memo == '') {
                throw new \Exception(trans('wallet.dqbztbbqbnwk'));
            }
           
            
            $rate=Setting::getValueByKey("withdraw_fee");
            if (empty($rate)){
                $rate=0;
            }
               
               
            $rate = bc_div($rate, 100);
            $wallet = UsersWallet::where('user_id', $user_id)
                ->where('currency', $currency_id)
                ->lockForUpdate()
                ->first();
            $balance_from = 3;//Setting::getValueByKey('withdraw_from_balance', 2); // 从哪个账户提币(1.法币,2.币币,3.杠杆)
            $balance_type = [
                1 => ['legal', '法币'],
                2 => ['change', '币币'],
                3 => ['lever', '杠杆币'],
            ];
            $balance_type2 = [
                1 => ['legal', 'Legal currency'],
                2 => ['change', 'Coin'],
                3 => ['lever', 'Leverage currency'],
            ];
            $balance_type3 = [
                1 => ['legal', '法幣'],
                2 => ['change', '幣幣'],
                3 => ['lever', '杠杆幣'],
            ];
            $balance_type4 = [
                1 => ['legal', '仏貨'],
                2 => ['change', '貨幣'],
                3 => ['lever', 'てこ貨幣'],
            ];
            $field_name = 'legal_balance';
            $balance_name = $balance_type[$balance_from][1];

            throw_if(bc_comp_zero($number) <= 0, new \Exception(trans('wallet.srdjebxdy')));
            //throw_if(bc_comp($number, $currency->min_number) < 0, new \Exception(trans('wallet.tbslbnxyzxz')));
            //throw_if(bc_comp($number, $currency->max_number) > 0 && bc_comp_zero($currency->max_number) > 0, new \Exception(trans('wallet.tbslbngyzdz')));
            $lang = $request->input('lang', 'en');
            if ($lang == 'zh_cn' || $lang == 'zh') {
                $balance_name = $balance_type[$balance_from][1];
                throw_if(bc_comp($number, $wallet->{$field_name}) > 0, new \Exception('余额不足'));
            } elseif ($lang == 'en') {
                $balance_name = $balance_type2[$balance_from][1];
                //throw_if(bc_comp($number, $wallet->{$field_name}) > 0, new \Exception('Sorry, your credit is running low'));
            } elseif ($lang == 'hk') {
                $balance_name = $balance_type3[$balance_from][1];
                throw_if(bc_comp($number, $wallet->{$field_name}) > 0, new \Exception('餘額不足'));
            } else if ($lang == 'jp') {
                $balance_name = $balance_type4[$balance_from][1];
                throw_if(bc_comp($number, $wallet->{$field_name}) > 0, new \Exception('残高が足りない'));
            } else {
                $balance_name = $balance_type2[$balance_from][1];
                //throw_if(bc_comp($number, $wallet->{$field_name}) > 0, new \Exception('Sorry, your credit is running low'));
            }

            $fee = bc_mul($number, $rate);
            $real_number = bc_sub($number, $fee);
            if ($lang == 'zh_cn') {
                $balance_name = $balance_type[$balance_from][1];
                throw_if(bc_comp_zero($real_number) <= 0, new \Exception($balance_name . '余额不足以支付手续费'));
            } elseif ($lang == 'en') {
                $balance_name = $balance_type2[$balance_from][1];
                throw_if(bc_comp_zero($real_number) <= 0, new \Exception($balance_name . 'The balance is not enough to pay the handling fee'));
            } elseif ($lang == 'hk') {
                $balance_name = $balance_type3[$balance_from][1];
                throw_if(bc_comp_zero($real_number) <= 0, new \Exception($balance_name . '餘額不足以支付手續費'));
            } else {
                $balance_name = $balance_type4[$balance_from][1];
                throw_if(bc_comp_zero($real_number) <= 0, new \Exception($balance_name . '残額は手数料を支払うのに足りません。'));
            }

            $walletOut = new UsersWalletOut();
            $walletOut->user_id = $user_id;
            $walletOut->currency = $currency_id;
            $walletOut->number = $number;
            $walletOut->address = $address;
            $walletOut->rate = $rate*100;
            $walletOut->real_number = $real_number;
            $walletOut->create_time = time();
            $walletOut->update_time = time();
            $walletOut->status = 1; //1提交提币2已经提币3失败
            $walletOut->type = $type; //协议类型
            $walletOut->notes = $label ?: $memo; //标签

            $order_no = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            $walletOut->order_no = $order_no;

            $walletOut->save();

            $result = change_wallet_balance($wallet, 0, -$number, AccountLog::WALLETOUT, '申请提币扣除余额', 'Apply for withdrawal deduction of balance');
            if ($result !== true) {
                throw new \Exception($result);
            }

            $result = change_wallet_lock_balance($wallet, 0, $number, AccountLog::WALLETOUT, '申请提币冻结余额', 'Apply for withdrawal and freeze balance', true);
            if ($result !== true) {
                throw new \Exception($result);
            }
            Redis::set('cashb_tip_count', 1);

            //event(new WithdrawSubmitEvent($walletOut));
            DB::commit();
            
            // 机器人推送消息
            robotSendMessage($user_id,'申请提现'.$number.'（'.$usdt_type.'）');
            
            return $this->success(trans('wallet.tbsqycgddsh'));
            //return $this->success(trans('wallet.tbsqycgddsh'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error(/*'File:' . $ex->getFile() . ',Line:'. $ex->getLine() . ',Message:'.*/ $ex->getMessage());
        }
    }

    //充币地址
    public function getWalletAddressIn()
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $currency_id = request()->input("currency", '');
        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }
        $wallet = Setting::getValueByKey('recharge_to_balance', '');
        if (empty($wallet)) {
            return $this->error(trans('wallet.cbbcz'));
        }
        $data = [
            'address' => $wallet,
            'bank' => Setting::getValueByKey('bank_name', ''),
            'real_name' => Setting::getValueByKey('bank_realname'),
            'card' => Setting::getValueByKey('bank_card_number')
        ];
        return $this->success($data);
    }

    //余额页面详情
    public function getWalletDetail()
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        // return $this->error('参数错误');
        $user_id = Users::getUserId();
        $currency_id = request()->input("currency", '');
        $type = request()->input("type", '');
        if (empty($user_id) || empty($currency_id)) {
            return $this->error(trans('wallet.cscw'));
        }
        $ExRate = Setting::getValueByKey('USDTRate', 6.5);
        // $userWallet = new UsersWallet();
        // return $this->error('参数错误');
        // $wallet = $userWallet->where('user_id', $user_id)->where('currency', $currency_id);

        $user_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first();

        // 注释掉全局钱包地址覆盖机制，避免安全风险
        /*
        if(empty($user_wallet['address']) || empty($user_wallet['erc20_address'])){
            $is_open_CTbi = Setting::getValueByKey('is_open_CTbi', '');
            $currency = Currency::where('id',$currency_id)->first();
            if($is_open_CTbi == '1' && $currency['name'] == 'USDT'){
                $user_wallet->address = Setting::getValueByKey('USDTAddress', '');
                $user_wallet->erc20_address = Setting::getValueByKey('USDTAddress_erc', '');
            }
            if($is_open_CTbi == '1' && $currency['name'] == 'BTC'){
                $user_wallet->address = Setting::getValueByKey('BTCAddress', '');

            }
            if($is_open_CTbi == '1' && $currency['name'] == 'ETH'){
                $user_wallet->address = Setting::getValueByKey('ETHAddress', '');

            }
            $user_wallet->save(); //默认生成所有币种的钱包
        }
        */


        if ($type == 'legal') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first();
        } else if ($type == 'change') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first();
        } else if ($type == 'lever') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first();
        } else if ($type == 'micro') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first();
        } else {
            return $this->error(trans('wallet.cscw'));
        }
        if (empty($wallet)) {
            return $this->error(trans('wallet.cbbcz'));
        }
        if($wallet->currency_name == 'USDT' || $wallet->currency_name == 'USDC') {
            $wallet['usdt_total'] = $wallet->change_balance;
        }else {
            $data = CurrencyQuotation::getCurrencyQuotationDetail($wallet->currency_name);
            if($data) {
                $wallet['usdt_total'] = bcmul($wallet->change_balance,$data->close,6);
            }
        }
       
        $wallet->ExRate = $ExRate;
        return $this->success($wallet);
    }

    public function legalLog(Request $request)
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $limit = $request->input('limit', 10);
        $account = $request->input('account', '');
        $currency = $request->input('currency', 0);
        $type = $request->input('type', '');//0资金账户；1币币账户；2合约账户 3 秒合约 4.理财
        $user_id = Users::getUserId();
        // $lang = Cache::get('lan_type_' . $user_id);

        $list = new AccountLog();
        if (!empty($currency)) {
            $list = $list->where('currency', $currency);
        }
        if (!empty($user_id)) {
            $list = $list->where('user_id', $user_id);
        }
        if (in_array($type,[0,1,2,3,4])) {
            $list = $list->whereHas('walletLog', function ($query) use ($type) {
                $query->where('balance_type', $type);
            });
        }
        $list = $list->orderBy('id', 'desc')->paginate($limit);
        //读取是否开启充提币
        $lang = request()->input('lang', 'en');
        if ($lang != 'zh_cn'&&$lang != 'zh'&&$lang != 'hk') {
            foreach ($list->items() as &$item) {
               $item['info'] = $item['en_info'];
            }
        }

        $is_open_CTbi = Setting::where("key", "=", "is_open_CTbi")->first()->value;
        return $this->success(array(
            "list" => $list->items(), 'count' => $list->total(),
            "limit" => $limit,
            "is_open_CTbi" => $is_open_CTbi,
            "lang"=>$lang
        ));
    }

    //提币记录
    public function walletOutLog()
    {
        $id = request()->input("id", '');
        $walletOut = UsersWalletOut::find($id);
        return $this->success($walletOut);
    }

    //接收来自钱包的PB
    public function getLtcKMB()
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $address = request()->input('address', '');
        $money = request()->input('money', '');
        $wallet = UsersWallet::whereHas('currencyCoin', function ($query) {
            $query->where('name', 'PB');
        })->where('address', $address)->first();
        if (empty($wallet)) {
            return $this->error(trans('wallet.cbbcz'));
        }
        DB::beginTransaction();
        try {

            $data_wallet1 = array(
                'balance_type' => 1,
                'wallet_id' => $wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' => $wallet->change_balance,
                'change' => $money,
                'after' => $wallet->change_balance + $money,
            );
            $wallet->change_balance = $wallet->change_balance + $money;
            $wallet->save();
            AccountLog::insertLog([
                'user_id' => $wallet->user_id,
                'value' => $money,
                'currency' => $wallet->currency,
                'info' => '转账来自钱包的余额',
                'info' => '转账来自钱包的余额',
                'type' => AccountLog::LTC_IN,
            ], $data_wallet1);
            DB::commit();
            return $this->success(trans('wallet.zzcg'));
        } catch (\Exception $rex) {
            DB::rollBack();
            return $this->error($rex);
        }
    }

    public function sendLtcKMB()
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $account_number = request()->input('account_number', '');
        $money = request()->input('money', '');
        if (empty($account_number) || empty($money) || $money < 0) {
            return $this->error(trans('wallet.cscw'));
        }
        $wallet = UsersWallet::whereHas('currencyCoin', function ($query) {
            $query->where('name', 'PB');
        })->where('user_id', $user_id)->first();
        if ($wallet->change_balance < $money) {
            return $this->error('余额不足');
        }

        DB::beginTransaction();
        try {

            $data_wallet1 = array(
                'balance_type' => 1,
                'wallet_id' => $wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' => $wallet->change_balance,
                'change' => $money,
                'after' => $wallet->change_balance - $money,
            );
            $wallet->change_balance = $wallet->change_balance - $money;
            $wallet->save();
            AccountLog::insertLog([
                'user_id' => $wallet->user_id,
                'value' => $money,
                'currency' => $wallet->currency,
                'info' => '转账余额至钱包',
                'type' => AccountLog::LTC_SEND,
            ], $data_wallet1);

            $url = "http://walletapi.bcw.work/api/ltcGet?account_number=" . $account_number . "&money=" . $money;
            $data = RPC::apihttp($url);
            $data = @json_decode($data, true);
            //            var_dump($data);die;
            if ($data["type"] != 'ok') {
                DB::rollBack();
                return $this->error($data["message"]);
            }
            DB::commit();
            return $this->success(trans('wallet.zzcg'));
        } catch (\Exception $rex) {
            DB::rollBack();
            return $this->error($rex->getMessage());
        }
    }

    //获取pb的余额交易余额
    public function PB()
    {
        $user_id = Users::getUserId();
        $wallet = UsersWallet::whereHas('currencyCoin', function ($query) {
            $query->where('name', 'PB');
        })->where('user_id', $user_id)->first();
        return $this->success($wallet->change_balance);
    }

    //币种余额
    public function coinWallet()
    {
        $user_id = Users::getUserId();
        $coin_id = request()->input('coin_id', '23');
        $user_walllet = UsersWallet::where('user_id', $user_id)->where('currency', $coin_id)->first();
        return $this->success($user_walllet);
    }
    // 设置提现密码
    public function savePayPasswrord() {
        $user_id = Users::getUserId();
        $pay_password = request()->input('pay_password', '');
    }
}
