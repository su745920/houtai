<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{AccountLog, Users, Setting, WalletSetting,WalletAddress,WalletAddressList};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App;
class AccountController extends Controller
{
    
    public function  todayProfitLoss(){
        $now=date("Y-m-d");
        $user_id = Users::getUserId();
        $sql="select  sum(t.fact_profits) as p  from  micro_orders  t where user_id=".$user_id."  and     DATE_FORMAT(created_at, '%Y-%m-%d') ='".$now."'";

        $kuiList=DB::select($sql);
        $kuiPO=$kuiList[0];
        $p=$kuiPO->p;
        if (empty($p)){
            $p=0;
        }
        $result["p"]=$p;
        return $this->success($result);

    }
    
      public function getCZAddressEn(Request $request)
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $lang = request()->input('lang', 'en');
        $cashType=request()->input('cashType','');

        if ($cashType=="usd"){
            $walletTypeList = [
                'usd' => [
                    'bankname', 'account_no', 'account_name', 'bank_address', 'swiftcode', 'company_address', 'erc_min', 'erc_max', 'voucher_switch', 'switch'
                ]
            ];
            $currencyList = [
                100 => 'usd',

            ];
        }
        if ($cashType=="eur"){
            $walletTypeList = [
                'eur' => [
                    'bankname', 'account_no', 'account_name', 'bank_address', 'swiftcode', 'company_address', 'erc_min', 'erc_max', 'voucher_switch', 'switch'
                ]
            ];
            $currencyList = [
                101 => 'eur',

            ];
        }
        if ($cashType=="gbp"){
            $walletTypeList = [
                'gbp' => [
                    'bankname', 'account_no', 'account_name', 'bank_address', 'swiftcode', 'company_address', 'erc_min', 'erc_max', 'voucher_switch', 'switch'
                ]
            ];
            $currencyList = [
                102 => 'gbp',

            ];
        }

        $currencyListRev = array_flip($currencyList);
        $settings = [];
        $availabelList = [];
        foreach ($walletTypeList as $walletType => $items) {
            foreach ($items as $item) {
                $settings[$walletType][$item] = '';
                $res = WalletSetting::where('wallet_type', $walletType)
                    ->where('parameter', $item)
                    ->first();
                if ($res) {
                    $settings[$walletType][$item] = $res->value;
                    if ($item == 'switch' && $res->value == 'on') {
                        $availabelList[] = (string)$currencyListRev[$walletType];
                    }
                }
            }
        }

        foreach ($settings as $walletType => $setting) {
            if ($walletType == 'gbp'||$walletType == 'usd'||$walletType == 'eur') {
                $jo["bank_name"] = $setting['bankname'];
                $jo["bank_account_no"] = $setting['account_no'];
                $jo["bank_receiver_name"] = $setting['account_name'];
                $jo["bank_address"] = $setting['bank_address'];
                $jo["bank_code"] = $setting['swiftcode'];
                $jo["bank_receiver_addr"] = $setting['company_address'];
                $jo["voucher_switch"] = $setting['voucher_switch'] == 'on';
            }

        }


        return $this->success($jo);


    }
    
    
    public function rechargeRecordV2()
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        try {
            if (empty($data['money']) || empty($data['address'])) {
                $this->success(trans('common.cscw'));
            }
            $user = Users::getAuthUser();
            if (empty($user->id)) throw new \Exception(trans('auth.wdl'));
            $USDTRate = Setting::getValueByKey('USDTRate', 7.2);
            //$voucher = $USDTRate*$data['money'];
            $dianhui = $data["dianhui"];
            $cashtype = "";
            $truename = "";
            if ($dianhui == 1) {
                $truename = $data["truename"];
            }
            $order_no = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);

            $currency = $data['currency'];
            if ($currency == 231) {
                $currency = 23;
            }
            //20221120 判断充币金额是否在钱包设置额度范围内
            $currencyList = [
                23 => 'usdt_erc',
                231 => 'usdt_trc',
                57 => 'usdc',
                32 => 'bit',
                35 => 'eth',
                58 => 'eft',
                581 => 'china_eft',
            ];
            //查找设置
            $walletSetting = [];
            if (!empty($currencyList[$data['currency']])) {
                $res = WalletSetting::where('wallet_type', $currencyList[$data['currency']])->get();
                if ($res) {
                    foreach ($res as $item) {
                        $walletSetting[$item->parameter] = $item->value;
                    }
                    if ($data['money'] < $walletSetting['erc_min']) {
                        return $this->error(trans('wallet.cbslxyzxed') . $walletSetting['erc_min']);
                    }
                    if ($data['money'] > $walletSetting['erc_max']) {
                        //return $this->error(trans('wallet.cbsldyzded').$walletSetting['erc_max']);
                    }
                }
            }
            
            $lang = request()->input('lang', 'en');
            
            $info = [
                'lang'=>$lang,
                'order_no' => $order_no,
                'truename' => $truename,
                'user_id' => $user->id,
                'phone' => $user->account_number,
                'address' => $data['address'],
                'money' => bcadd($data['money'],0,6,PHP_ROUND_HALF_DOWN),
                'cashtype' => $cashtype,
                'currency' => $currency,
                'voucher' => $data['voucher'],
                'dianhui' => $dianhui,
                'created_at' => date('Y-m-d H:i:s', time()),
                'updated_at' => date('Y-m-d H:i:s', time()),
                'usdt_type' => $data['usdt_type']
            ];
            $res = DB::table('recharge_record')->insert($info);

            // $count = Redis::get("recharge_tip_count");
            // $count = bcadd($count,1);
            //Redis::set('recharge_tip_count', 1);
            
            $fromBank = request()->input('fromBank', '');
            if ($fromBank=="1") {
                $resultInfo["tradeNo"] = $order_no;
                $resultInfo["userId"] = $user->id;
                $resultInfo["userName"] = $user->account_number;

                return $this->success($resultInfo);
            }
            if ($res) return $this->success(trans('wallet.tjcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    public function getCZAddressV2(Request $request)
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $lang = request()->input('lang', 'en');
        $walletTypeList = [
            'china_eft' => [
                'bankname', 'account_no', 'account_name', 'erc_min', 'erc_max', 'voucher_switch', 'switch'
            ],
        ];
        $currencyList = [

            581 => 'china_eft',
        ];
        if ($lang=="vi"){
            $walletTypeList = [
                'vi' => [
                    'bankname', 'account_no', 'account_name', 'bank_address', 'swiftcode', 'company_address', 'erc_min', 'erc_max', 'voucher_switch', 'switch'
                ]
            ];
            $currencyList = [
                58 => 'vi',

            ];
        }
        if ($lang=="id"){
            $walletTypeList = [
                'id' => [
                    'bankname', 'account_no', 'account_name', 'bank_address', 'swiftcode', 'company_address', 'erc_min', 'erc_max', 'voucher_switch', 'switch'
                ]
            ];
            $currencyList = [
                58 => 'id',

            ];
        }
        if ($lang=="th"){
            $walletTypeList = [
                'th' => [
                    'bankname', 'account_no', 'account_name', 'bank_address', 'swiftcode', 'company_address', 'erc_min', 'erc_max', 'voucher_switch', 'switch'
                ]
            ];
            $currencyList = [
                58 => 'th',

            ];
        }

        $currencyListRev = array_flip($currencyList);
        $settings = [];
        $availabelList = [];
        foreach ($walletTypeList as $walletType => $items) {
            foreach ($items as $item) {
                $settings[$walletType][$item] = '';
                $res = WalletSetting::where('wallet_type', $walletType)
                    ->where('parameter', $item)
                    ->first();
                if ($res) {
                    $settings[$walletType][$item] = $res->value;
                    if ($item == 'switch' && $res->value == 'on') {
                        $availabelList[] = (string)$currencyListRev[$walletType];
                    }
                }
            }
        }

        foreach ($settings as $walletType => $setting) {
            if ($walletType == 'id'||$walletType == 'th'||$walletType == 'vi') {
                $jo["bank_name"] = $setting['bankname'];
                $jo["bank_account_no"] = $setting['account_no'];
                $jo["bank_receiver_name"] = $setting['account_name'];
                $jo["bank_address"] = $setting['bank_address'];
                $jo["bank_code"] = $setting['swiftcode'];
                $jo["bank_receiver_addr"] = $setting['company_address'];
                $jo["voucher_switch"] = $setting['voucher_switch'] == 'on';
            } else if ($walletType == 'china_eft') {
                $jo["bank_name"] = $setting['bankname'];
                $jo["bank_account_no"] = $setting['account_no'];
                $jo["bank_receiver_name"] = $setting['account_name'];
                $jo["bank_address"] = '';
                $jo["bank_code"] = '';
                $jo["bank_receiver_addr"] = '';
                $jo["voucher_switch"] = $setting['voucher_switch'] == 'on';
            } else {
                $jo["address"] = $setting['address'];
                $jo["voucher_switch"] = $setting['voucher_switch'] == 'on';
                //20221122 统一返回空值
                $jo["network"] = "";//$noteList[$walletType];
            }

        }


        return $this->success($jo);


    }
    
    public function list()
    {
        //$address = Users::getUserId(request()->input('address', ''));
        $limit = request()->input('limit', '12');
        $page = request()->input('page', '1');
//        if (empty($address)) {
//            return $this->error("参数错误");
//        }
//        $user = Users::where("id", $address)->first();
//        if (empty($user)) {
//            return $this->error("数据未找到");
//        }
        $user_id = Users::getUserId();
        $lang = Cache::get('lan_type_'.$user_id);
        $data = AccountLog::where("user_id", $user_id)->orderBy('id', 'DESC')->paginate($limit);
        
        foreach ($data as $po){
            $info=$po->info;
            $list=explode(',',$info);
            if (!empty($list)&&count($list)>0){
                $str = $list[0];
                if(strpos($str,'(')>0) $str = substr($str,0,strpos($str,'('));
                preg_match ('/[A-Za-z\/]+/',$str,$result);
                if(count($result)>0){
                        $str = str_replace($result[0],' ',$str);
                        $po->info=$str;
                        $po->info_ext=$result[0];
                }
                else{   
                        $po->info=$str;
                        $po->info_ext='';
                }
            }

        }
        
        // if($lang != 'zh_cn'){
        //     foreach ($data as $key=>$value){
        //         $data[$key]['info'] = $value['en_info'];
        //     }
        // }else{
        //     foreach ($data as $key=>$value){
        //         $data[$key]['info'] = $value['en_info'];
        //     }
        // }
        return $this->success(array(
            "user_id" => $user_id,
            "data" => $data->items(),
            "limit" => $limit,
            "page" => $page,
            'lang' =>$lang,
        ));
    }

    public function show_profits(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->input('limit', 10);
        $prize_pool = AccountLog::whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number');
            if ($account_number) {
                $query->where('account_number', $account_number);
            }
        })->where(function ($query) use ($request) {
            $start_time = strtotime($request->input('start_time', null));
            $end_time = strtotime($request->input('end_time', null));
            $start_time && $query->where('created_time', '>=', $start_time);
            $end_time && $query->where('created_time', '<=', $end_time);
        })->where("type", AccountLog::PROFIT_LOSS_RELEASE)->where("user_id", "=", $user_id)->orderBy('id', 'desc')->paginate($limit);

        return $this->success($prize_pool);
    }
    
    public function record(){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $data = \request()->all();
        try {
            $user = Users::getAuthUser();
            if (empty($user->id)) throw new \Exception(trans('auth.wdl'));
            $dianhui=$data["dianhui"];
            $info = [
                 'dianhui'       => $dianhui,
                'user_id'       => $user->id,
                'phone'         => $user->phone,
                // 'address'       => $data['address'],
                'money'         => $data['money'],
                'voucher'       => $data['voucher'],
                'created_at'    => date('Y-m-d H:i:s',time()),
                'updated_at'    => date('Y-m-d H:i:s',time())
            ];
            $res = DB::table('recharge_record')->insert($info);
            if ($res) return $this->success(trans('common.tjcg'));else return $this->error(trans('common.tjsb'));
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }
    
    public function rechargeRecord(){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        
        $data = \request()->all();
        try {
            if(empty($data['money']) || empty($data['address'])){
                $this->success(trans('common.cscw'));
            }
            
            // 判断是否开启了实名认证校验
            if(Setting::getValueByKey("is_open_transaction",0) === 1) {
                // 判断是否高级实名认证
                $advanced_review_status = 0;
                $user_id = Users::getUserId();
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
            
            $user = Users::getAuthUser();
            if (empty($user->id)) throw new \Exception(trans('auth.wdl'));
            $USDTRate = Setting::getValueByKey('USDTRate', 7.2);
            //$voucher = $USDTRate*$data['money'];
            $dianhui=$data["dianhui"];
            $cashtype="";
            $truename="";
            if ($dianhui==1){
                $cashtype=$data["cashtype"];
                $truename=$data["truename"];
            }
            $order_no= date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            
            $currency=$data['currency'];
            if ($currency==231){
                $currency=23;
            }
            //20221120 判断充币金额是否在钱包设置额度范围内
            $currencyList = [
                23  => 'usdt_erc',
                231  => 'usdt_trc',
                57  => 'usdc',
                32  => 'bit',
                35  => 'eth',
                58  => 'eft',
                581  => 'china_eft',
            ];
            //查找设置
            $walletSetting = [];
            if(!empty($currencyList[$data['currency']])){
                $res = WalletSetting::where('wallet_type',$currencyList[$data['currency']])->get();
                if($res){
                    foreach($res as $item){
                        $walletSetting[$item->parameter] = $item->value;
                    }       
                    if($data['money'] < $walletSetting['erc_min']){
                        return $this->error(trans('wallet.cbslxyzxed').$walletSetting['erc_min']);
                    }
                    if($data['money'] > $walletSetting['erc_max']){
                        return $this->error(trans('wallet.cbsldyzded').$walletSetting['erc_max']);
                    }        
                }
            } 
            
            // 查找充值币种
            $wallet_address_list_info = WalletAddressList::where('id',$data['id'])->where('is_show',1)->first();
            if(empty($wallet_address_list_info)) {
                return $this->error(trans('wallet.cbzbcz'));
            }
            
            // 判断充值范围
            if ($data['money'] < $wallet_address_list_info['min_limit']) {
                return $this->error(trans('wallet.cbslxyzxed') . $wallet_address_list_info['min_limit']);
            }
            if ($data['money'] > $wallet_address_list_info['max_limit']) {
                return $this->error(trans('wallet.cbsldyzded').$wallet_address_list_info['max_limit']);
            }
            
            // 判断是否需要凭证
            if($wallet_address_list_info['is_voucher'] == 1) {
                if(empty($data['voucher'])) {
                    $this->error(trans('qsczfpz'));
                }
            }
            
            $str = $data['voucher'];
            $prefix = "https://";

            if (strncmp($str, $prefix, strlen($prefix)) === 0) {
            }else{
                $str="";
            }

            if (strpos($str, "<") !== false) {
                $str="";
            }

            if (strpos($truename, "<") !== false) {
                $truename="";
            }
            $address= $data['address'];
            if (strpos($address, "<") !== false) {
                $address="";
            }

            $money=  bcadd($data['money'],0,6);
            if (strpos($money, "<") !== false) {
                $money="";
            }


            if (strpos($cashtype, "<") !== false) {
                $cashtype="";
            }
            if (strpos($currency, "<") !== false) {
                $currency="";
            }

            if (strpos($dianhui, "<") !== false) {
                $dianhui="";
            }
            $usdt_type= $data['usdt_type'];
            if (strpos($usdt_type, "<") !== false) {
                $usdt_type="";
            }
            
            
            $info = [
                'order_no'   =>  $order_no,
                'truename'     =>$truename,
                'user_id'       => $user->id,
                'phone'         => $user->account_number,
                'address'       => $address,
                'money'         => $money,
                'cashtype'      =>$cashtype,
                'currency'      => $currency,
                 'voucher'       => $str,
                'dianhui'       => $dianhui,
                'created_at'    => date('Y-m-d H:i:s',time()),
                'updated_at'    => date('Y-m-d H:i:s',time()),
                 'usdt_type'     =>$usdt_type
            ];

            $res = DB::table('recharge_record')->insert($info);
            
            // 机器人推送消息
            robotSendMessage($user->id,'申请充值'.$money.'（'.$usdt_type.'）');
            
            // $count = Redis::get("recharge_tip_count");
            // $count = bcadd($count,1);
            Redis::set('recharge_tip_count', 1);
            
            //  try{
            //   $result = file_get_contents('https://api0912.myshop0816.shop/market/binance/stomp/push');
            //   json_decode($result, true);
            // }catch (\Exception $e) {}

        
            if ($res) return $this->success(trans('wallet.tjcg'));else return $this->error(trans('wallet.tjsb'));
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }
    
    public function recordList(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $limit = $request->input('limit', 10);
        $user_id = Users::getUserId();
        //$lists = DB::table('recharge_record')->where('user_id',$user_id)->paginate($limit);
        $lists = DB::table('recharge_record')->where('user_id',$user_id) ->orderBy('id', 'desc')->paginate($limit);
        foreach ($lists as $po){
            $currency=$po->currency;
            if ($currency==23){
                $po->coin="USDT";
            }
            if ($currency==57){
                $po->coin="USDC";
            }
            if ($currency==32){
                $po->coin="BTC";
            }
            if ($currency==35){
                $po->coin="ETH";
            }
        }
        return $this->success($lists);
    }
    public function recordDetail(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $id = $request->input('id');
        $data = DB::table('recharge_record')->where('user_id',$user_id)->where('id',$id)->first();
        if(empty($data)) {
            return $this->error('Error'); 
        }
        return $this->success($data);
    }
    
    public function getCZAddress(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $currency = $request->input('currency');
        $value="";
        $walletTypeList = [
            'usdt_trc' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'usdt_erc' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'usdc' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'eth' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'bit' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'eft' => [
                'bankname','account_no','account_name','bank_address','swiftcode','company_address','erc_min','erc_max','voucher_switch','switch'
            ],
            'china_eft' => [
                'bankname','account_no','account_name','erc_min','erc_max','voucher_switch','switch'
            ],
        ];
        $currencyList = [
            23  => 'usdt_erc',
            231  => 'usdt_trc',
            57  => 'usdc',
            32  => 'bit',
            35  => 'eth',
            58  => 'eft',
            581  => 'china_eft',
        ];
        $noteList = [
            'usdt_trc' => 'TRC20',
            'usdt_erc' => 'ERC20',
            'usdc' => '',
            'bit' => 'bitcoin',
            'eth' => 'ERC20',
            'eft' => '',
            'china_eft' => '',
        ];
        $currencyListRev = array_flip($currencyList);
        $settings = [];
        $availabelList = [];
        foreach($walletTypeList as $walletType => $items){
            foreach($items as $item){
                $settings[$walletType][$item] = '';
                $res = WalletSetting::where('wallet_type',$walletType)
                ->where('parameter',$item)
                ->first();
                if($res){
                    $settings[$walletType][$item] = $res->value;
                    if($item == 'switch' && $res->value == 'on'){
                        $availabelList[] = (string)$currencyListRev[$walletType];
                    }
                } 
            }
        }
        if(!empty($currency)){
            foreach($settings as $walletType => $setting){
                if($walletType == $currencyList[$currency]){
                    if($walletType == 'eft'){
                        $jo["bank_name"] = $setting['bankname'];
                        $jo["bank_account_no"] = $setting['account_no'];
                        $jo["bank_receiver_name"] = $setting['account_name'];
                        $jo["bank_address"] = $setting['bank_address'];
                        $jo["bank_code"] = $setting['swiftcode'];
                        $jo["bank_receiver_addr"] = $setting['company_address'];
                        $jo["voucher_switch"] = $setting['voucher_switch']=='on';
                    }
                    else if($walletType == 'china_eft'){
                        $jo["bank_name"] = $setting['bankname'];
                        $jo["bank_account_no"] = $setting['account_no'];
                        $jo["bank_receiver_name"] = $setting['account_name'];
                        $jo["bank_address"] = '';
                        $jo["bank_code"] = '';
                        $jo["bank_receiver_addr"] = '';
                        $jo["voucher_switch"] = $setting['voucher_switch']=='on';    
                    }
                    else{
                        // 查询用户
                        $user_id = Users::getUserId();
                        $user = Users::findOrFail($user_id);
                        $wallet_address_id_type = '';
                        if($user) {
                            // 查询充值地址
                            $address = '';
                            switch($currency) {
                                case 231: 
                                    $wallet_address_id_type = 'usdt_trc_address';
                                break;
                                case 23: 
                                    $wallet_address_id_type = 'usdt_erc_address';
                                break;
                                case 35: 
                                    $wallet_address_id_type = 'eth_address';
                                break;
                                case 32: 
                                    $wallet_address_id_type = 'btc_address';
                                break;
                                case 57: 
                                    $wallet_address_id_type = 'usdc_address';
                                break;
                            }
                            // 默认的
                            $default_address = WalletAddress::where('is_default',1)->first();
                            if($default_address) {
                                $address = $default_address[$wallet_address_id_type];
                            }
                            // 查询用户充值地址
                            $user_wallet_address = WalletAddress::where('id',$user->wallet_address_id)->first();
                            if($user_wallet_address) {
                                $address = $user_wallet_address[$wallet_address_id_type];
                            }
                        }
                        // $jo["address"] = $setting['address'];
                        // 改
                        $jo["address"] = $address;
                        $jo["voucher_switch"] = $setting['voucher_switch']=='on';
                        //20221122 统一返回空值
                        $jo["network"] = "";//$noteList[$walletType];
                    }
                }
            }
        }
        else{
            $jo["address"] = $availabelList;
        }
        /*
        if ($currency==23){
            $value=Setting::getValueByKey('USDTAddress_erc');
            $jo["address"]=$value;
            $jo["network"]="TRC20";
            return $this->success($jo);
        }
        if ($currency==57){
            $value=Setting::getValueByKey('USDCAddress');
            $jo["address"]=$value;
            $jo["network"]="";
            return $this->success($jo);
        }

        if ($currency==231){
            $value=Setting::getValueByKey('USDTAddress');
            $jo["address"]=$value;
            $jo["network"]="TRC20";
            return $this->success($jo);
        }
        if ($currency==32){
            $vo=Setting::getVOByKey('BTCAddress');

            $jo["address"]=$vo->value;
            $jo["network"]=$vo->notes;
            return $this->success($jo);
        }
        if ($currency==35){
            $vo=Setting::getVOByKey('ETHAddress');

            $jo["address"]=$vo->value;
            $jo["network"]=$vo->notes;
            return $this->success($jo);

        }
        if($currency==58){
           $vo=Setting::getVOByKey('USDAddress');
           $detail=explode(';',$vo->value);
           $jo["bank_name"]=$detail[0];
           $jo["bank_account_no"]=$detail[1];
           $jo["bank_receiver_name"]=$detail[2];
           $jo["bank_address"]=$detail[3];
           $jo["bank_code"]=$detail[4];
           $jo["bank_receiver_addr"]=$detail[5];
           return $this->success($jo);
        }
        $vo=Setting::getVOByKey('AvailableChargeAddress');
        $jo["address"]=explode(',',$vo->value);
        */
        return $this->success($jo);


    }
    // 获取还款地址
    public function getRepaymentAddress(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $currency = $request->input('currency');
        $value="";
        $walletTypeList = [
            'usdt_trc_repayment' => [
                'address','switch'
            ],
            'usdt_erc_repayment' => [
                'address','switch'
            ],
            'usdc_repayment' => [
                'address','switch'
            ],
            'eth_repayment' => [
                'address','switch'
            ],
            'bit_repayment' => [
                'address','switch'
            ]
        ];
        $currencyList = [
            23  => 'usdt_erc_repayment',
            231  => 'usdt_trc_repayment',
            57  => 'usdc_repayment',
            32  => 'bit_repayment',
            35  => 'eth_repayment',
        ];
        $currencyListRev = array_flip($currencyList);
        $settings = [];
        $availabelList = [];
        foreach($walletTypeList as $walletType => $items){
            foreach($items as $item){
                $settings[$walletType][$item] = '';
                $res = WalletSetting::where('wallet_type',$walletType)
                ->where('parameter',$item)
                ->first();
                if($res){
                    $settings[$walletType][$item] = $res->value;
                    if($item == 'switch' && $res->value == 'on'){
                        $availabelList[] = (string)$currencyListRev[$walletType];
                    }
                } 
            }
        }
        if(!empty($currency)){
            foreach($settings as $walletType => $setting){
                if($walletType == $currencyList[$currency]){
                    $jo["address"] = $setting['address'];
                    $jo["switch"] = $setting['switch']=='on';
                }
            }
        }
        else{
            $jo["address"] = $availabelList;
        }
        return $this->success($jo);


    }
    
}
