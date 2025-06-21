<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FromQueryExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{Address,
    AccountLog,
    Bankcard,
    Currency,
    PaymentMethod,
    Setting,
    Users,
    UserCashInfo,
    UserReal,
    UsersWallet,
    BlindBoxOrder,
    LeverTransaction,
    LockMiningOrder,
    MicroOrder,
    RechargeRecord,
    WalletLog,
    MarketHour,
    CreditLog,
    Mail,
    WalletAddress,
    Agent,
    CurrencyQuotation,
    AdminRole
};
    use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    
    public function walletBalanceFn($user_id,$lang)
    {
        if (empty($lang)){
            $lang="vi";
        }
        $currency_name = "";
        $currency_id = 63;
        $settingVO = 7.19;

        $USDTRate = Setting::getValueByKey('USDTRate', 7.22);

        $cache_key_name = "user_wallet_data_{$user_id}";

        $user_wallet = UsersWallet::with(['currencyCoin'])->where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {

                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');

            })->get();
        
        // $user_wallet = UsersWallet::where('user_id', $user_id)
        //     ->orWhere('legal_balance','>',0)
        // ->orWhere('lever_balance','>',0)
        // ->orWhere('change_balance','>',0)
        // ->orWhere('micro_balance','>',0)
        // ->orWhere('earn_balance','>',0)->get();
        

        $user_wallet->transform(function ($item, $key) {
            $item->setVisible([
                'id', 'currency', 'currency_name', 'sort',
                'usdt_price',
                'legal_balance',
                'lever_balance',
                'change_balance',
                'micro_balance',
                'earn_balance'

            ]);
            return $item;
        });

        

        $settingVO_vi = Setting::getValueByKey('vnd');
        $settingVO_th = Setting::getValueByKey('thb');
        $settingVO_id = Setting::getValueByKey('idr');
        $settingVO_zh = Setting::getValueByKey('rmb');


        $totalU=0;
        foreach ($user_wallet as $k => $v) {
            $num = $v['legal_balance'];
            $lever_balance=$v['lever_balance'];
            if (empty($lever_balance)){
                $lever_balance=0;
            }
            $change_balance=$v['change_balance'];
            if (empty($change_balance)){
                $change_balance=0;
            }
            $micro_balance=$v['micro_balance'];
            if (empty($micro_balance)){
                $micro_balance=0;
            }
            $earn_balance=$v['earn_balance'];
            if ($earn_balance){
                $earn_balance=0;
            }


            $currency = $v['currency'];
            if ($currency == 23) {
                $totalU=$totalU+$num;
                $totalU=$totalU+$lever_balance;
                $totalU=$totalU+$change_balance;

                $totalU=$totalU+$micro_balance;
                $totalU=$totalU+$earn_balance;


            } else {
                if ($lang == "vi") {
                    if ($currency == 81) {

                        $tmp = bc_div($num, $settingVO_vi);
                        $totalU=$totalU + floatval($tmp);
                    } else {
                        $tmp = $num * $v['usdt_price'];
                        $totalU=$totalU + floatval($tmp);
                        //
                        $totalU=$totalU+$v['usdt_price']*$lever_balance;
                        $totalU=$totalU+$v['usdt_price']*$change_balance;

                        $totalU=$totalU+$v['usdt_price']*$micro_balance;
                        $totalU=$totalU+$v['usdt_price']*$earn_balance;


                    }


                } else if ($lang == "th") {

                    if ($currency == 82) {

                        $tmp = bc_div($num, $settingVO_th);
                        $totalU=$totalU + floatval($tmp);
                    } else {
                        $tmp = $num * $v['usdt_price'];
                        $totalU=$totalU + floatval($tmp);
                        //
                        $totalU=$totalU+$v['usdt_price']*$lever_balance;
                        $totalU=$totalU+$v['usdt_price']*$change_balance;

                        $totalU=$totalU+$v['usdt_price']*$micro_balance;
                        $totalU=$totalU+$v['usdt_price']*$earn_balance;
                    }

                } else if ($lang == "id") {
                    if ($currency == 83) {

                        $tmp = bc_div($num, $settingVO_id);
                        $totalU=$totalU + floatval($tmp);
                    } else {
                        $tmp = $num * $v['usdt_price'];
                        $totalU=$totalU + floatval($tmp);
                        //
                        $totalU=$totalU+$v['usdt_price']*$lever_balance;
                        $totalU=$totalU+$v['usdt_price']*$change_balance;

                        $totalU=$totalU+$v['usdt_price']*$micro_balance;
                        $totalU=$totalU+$v['usdt_price']*$earn_balance;
                    }
                } else {

                    if ($currency == 63) {

                        $tmp = bc_div($num, $settingVO_zh);
                        $totalU=$totalU + floatval($tmp);

                    } else {
                        $tmp = $num * $v['usdt_price'];
                        $totalU=$totalU + floatval($tmp);
                        //
                        $totalU=$totalU+$v['usdt_price']*$lever_balance;
                        $totalU=$totalU+$v['usdt_price']*$change_balance;

                        $totalU=$totalU+$v['usdt_price']*$micro_balance;
                        $totalU=$totalU+$v['usdt_price']*$earn_balance;
                    }
                }


            }
        }






        return $totalU;
    }


    public function editFKRatio(Request $request){
        $id = $request->input('id', 0);
        $result=Users::getById($id);
        //


        return view("admin.user.fkRatio",[ 'result' => $result]);
    }

    public function updateFKRatio(Request $request){
        $id = $request->input('id', 0);
        $result=Users::getById($id);
        $subcontrol_ratio=$request->input("subcontrol_ratio");
        $result->subcontrol_ratio=$subcontrol_ratio;
        //
        $risk=$request->input('risk');
        $result->risk=$risk;



        $result->save();
        return $this->success('操作成功');
    }


    public function index()
    {
        $authorityList=session()->get("authorityList");
        return view("admin.user.index",['authorityList' =>$authorityList]);
    }

    //导出用户列表至excel
    public function csv()
    {
        $query = Users::query();
        return Excel::download(new FromQueryExport($query), '用户数据.xlsx');
    }

    //用户列表
    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
      
        $id = $request->get('id', '');
        $account = $request->get('account', '');
        $name = $request->get('name', '');
        $risk = $request->get('risk', -2);

        $list = new Users();
        //$list = $list->leftjoin("user_real", "users.id", "=", "user_real.user_id");

        if (!empty($id)) {
            $list = $list->where('users.id', $id);
        }
        if (!empty($account)) {
            $list = $list->where("phone", 'like', '%' . $account . '%')
                ->orwhere('email', 'like', '%' . $account . '%')
                ->orWhere('account_number', 'like', '%' . $account . '%');
        }

        $list = $list->when($name != '', function ($query) use ($name) {
            $query->whereHas('userReal', function ($query) use ($name) {
                $query->where('name', $name);
            });
        });

        if ($risk != -2) {
            $list = $list->where('risk', $risk);
        }
        
        $start_time=$request->get('start_time','');
        if (!empty($start_time)) {
            $startTime=strtotime($start_time);
            $list = $list->where('time','>', $startTime);
        }
        
        $end_time=$request->get('end_time','');
        if (!empty($end_time)) {
            $endTime=strtotime($end_time);
            $list = $list->where('time','<', $endTime);
        }

        $list = $list->select("users.id", "users.account_number","users.email","users.country_code","users.parent_id", "users.extension_code","users.time","users.risk", "users.credit_score", "users.remark","users.status","users.password", "users.pay_password","users.cz_count"
            ,"users.ip","users.lang",'users.google_secret','users.agent_note_id','users.is_realname')
            ->orderBy('users.id', 'desc')->paginate($limit);
       
        $items = $list->getCollection();
        $items->transform(function ($item, $key) {
            return $item->append('risk_name','real');
        });
        $list->setCollection($items);
        
    //   $settingVO_vi = Setting::getValueByKey('vnd');
    //     $settingVO_vi=floatval($settingVO_vi);
    //     $settingVO_th = Setting::getValueByKey('thb');
    //     $settingVO_th=floatval($settingVO_th);
    //     $settingVO_id = Setting::getValueByKey('idr');
    //     $settingVO_id=floatval($settingVO_id);
    //     $settingVO_zh = Setting::getValueByKey('rmb');
    //     $settingVO_zh=floatval($settingVO_zh);
        
       
        
        foreach ($list as $vo){
            
            $uid=$vo->id;
            $userStr="#".$uid.'#';
            $t=Redis::get($userStr);
            $vo->t=$t;
            if ($t&&(strlen($t)>5||$t=="online")){
                $vo->onlineStatus="在线";
            }else{
                $vo->onlineStatus="离线";
            }
            
            $country_code=$vo->country_code;
            $uid=$vo->id;
            $lang=$vo->lang;
            if (empty($country_code)||"undefined"==$country_code){
                $vo->country_code="";
            }
            // $totalU=$this->walletBalanceFn($uid,"vi");

            // if ($lang=="vi"){
            //     $totalFB=$settingVO_vi * $totalU;
            //     $totalFB=number_format($totalFB, 2);
            //     $vo->totalFB=$totalFB."VND";
            // } else if ($lang=="th"){
            //     $totalFB=$settingVO_vi * $totalU;
            //     $totalFB=number_format($totalFB, 2);
            //     $vo->totalFB=$totalFB."THB";
            // } else if ($lang=="id"){
            //     $totalFB=$settingVO_vi * $totalU;
            //     $totalFB=number_format($totalFB, 2);
            //     $vo->totalFB=$totalFB."IDR";
            // }else{
            //     $totalFB=$settingVO_zh * $totalU;
            //     $totalFB=number_format($totalFB, 2);
            //     $vo->totalFB=$totalFB."CNY";
            // }
            // $totalU=number_format($totalU, 2);
            // $vo->totalU=$totalU;
        }


        //$USDT_id = Currency::where('name', 'USDT')->first()->id;


        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
    
    public function usersUsdt(Request $request) {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }
        $totalU=$this->walletBalanceFn($id,"vi");
        $totalU = number_format($totalU, 2);
        return response()->json(['code' => 0, 'data' => $totalU]);
    }
    
    public function rechargeUsdt(Request $request) {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }
        
        $list = AccountLog::where('user_id',$id)->where('type',AccountLog::WALLET_CURRENCY_IN)->get();
        $totalU = 0;
        foreach ($list as $k => $v) {
            // 获取价格
            if($v->currency !== 23) {
                $currency = CurrencyQuotation::where('currency_id', $v->currency)->select(['close'])->first();
                $last_price = $currency->close;
            }else {
                $last_price = 1;
            }
            $totalU = bcadd($totalU,bcmul($v->value,$last_price));
        }
        $totalU = number_format($totalU, 2);
        return response()->json(['code' => 0, 'data' => $totalU]);
    }
    
    public function withdrawUsdt(Request $request) {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }
        
        $list = AccountLog::where('user_id',$id)->where('type',AccountLog::WALLETOUTDONE)->get();
        $totalU = 0;
        foreach ($list as $k => $v) {
            // 获取价格
            if($v->currency !== 23) {
                $currency = CurrencyQuotation::where('currency_id', $v->currency)->select(['close'])->first();
                $last_price = $currency->close;
            }else {
                $last_price = 1;
            }
            $totalU = bcadd($totalU,bcmul($v->value,$last_price));
            $totalU = abs($totalU);
        }
        $totalU = number_format($totalU, 2);
        return response()->json(['code' => 0, 'data' => $totalU]);
    }
    
    public function addressList(Request $request) {
        $limit = $request->get('limit', 10);
        $id = $request->input('id', 0);
        $list = PaymentMethod::where('uid', $id)
                ->orderBy('id', 'desc')
                ->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
    
    public function edit(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }
        $result = Users::leftjoin("user_real", "users.id", "=", "user_real.user_id")
            ->select("users.*", "user_real.card_id", "user_real.name")
            ->findOrFail($id);
        $cashinfo = UserCashInfo::unguarded(function () use ($id) {
            return UserCashInfo::firstOrNew(['user_id' => $id]);
        });
        $bankPO=Bankcard::where('uid',$id)->first();
        
        if (empty($bankPO)){
            $bankPO=new Bankcard();
        }
        
        $wallet_address_list = WalletAddress::where('is_show',1)->get();
        $agent_list = Agent::where('parent_agent_id',1)->get();
        
        
        $admin_role_id = session()->get('admin_role_id');
        $adminRole = AdminRole::find($admin_role_id);
        if ($adminRole == null) {
            abort(404);
        }
        $authorityList=$adminRole->authortityList;//
        
        return view('admin.user.edit', [
            'result' => $result,
            'cashinfo' => $cashinfo,
            'bankPO'=>$bankPO,
            'wallet_address_list' => $wallet_address_list,
            'agent_list' => $agent_list,
            'authorityList' => $authorityList,
        ]); 
    }

    //编辑用户信息  
    public function doedit()
    {
        $id = request()->input("id");
        $name = request()->input("name", '');
        $card_id = request()->input("card_id", '');
        $password = request()->input("password", '');
        $account_number = request()->input("account_number", '');
        $pay_password = request()->input("pay_password", '');
        $credit_score = request()->input("credit_score", '');
        $remark = request()->input("remark", '');
        $bank_account = request()->input("bank_account", '');
        $bank_name = request()->input("bank_name", '');
        $alipay_account = request()->input("alipay_account", '');
        $wechat_nickname = request()->input("wechat_nickname", '');
        $wechat_account = request()->input("wechat_account", '');
        $wechat_collect = request()->input("wechat_collect", '');
        $alipay_collect = request()->input("alipay_collect", '');

        $truename = request()->input("truename", '');      
        $provincecity = request()->input("provincecity", '');
        $store = request()->input("store", '');
        $idcard = request()->input("idcard", '');
        $international_code = request()->input("international_code", '');
        $link_mp = request()->input("link_mp", '');

        $email=request()->input("email");
        $phone=request()->input("phone");
        
        $wallet_address_id=request()->input("wallet_address_id");
        $agent_note_id=request()->input("agent_note_id");
        
        if (empty($id)) {
            return $this->error("参数错误");
        }

        try {
            DB::beginTransaction();
            // 用户账号
            $user = Users::findOrFail($id);
            $user->account_number = $account_number;
            $password != '' && $user->password = Users::MakePassword($password);
            $pay_password != '' && $user->pay_password = Users::MakePassword($pay_password);

            if($user->credit_score != $credit_score){
                $creditLog = New CreditLog();
                $creditLog->user_id = $id;
                $creditLog->lock_mining_order_id = 0;
                $creditLog->before = $user->credit_score;
                $creditLog->after = $credit_score;
                $creditLog->change = $credit_score - $user->credit_score;
                $creditLog->memo = "后台修改信用分";
                $creditLog->en_memo = 49;
                $creditLog->save();
            }

            $user->credit_score = $credit_score;
            $user->remark = $remark;

            if (!empty($email)&&strlen($email)>0){
                $user->email=$email;
            }
            if (!empty($phone)&&strlen($phone)>0){
                $user->phone=$phone;
            }
            
            // 充值地址信息
            $user->wallet_address_id=$wallet_address_id;
            
            // 所属代理
            // 判断是否给代理本人分配
            if(empty($agent_note_id)) {
                $user->agent_note_id = '';
            }else {
                if($user->agent_id == $agent_note_id) {
                    throw new \Exception('所属代理不能为代理本人');
                }
                $user->agent_note_id = $agent_note_id;
            }
        

            $user->save();
            // 收款信息
            $cashinfo = UserCashInfo::unguarded(function () use ($id) {
                return UserCashInfo::firstOrNew(['user_id' => $id]);
            });
            $bank_name != '' && $cashinfo->bank_name = $bank_name;
            $bank_account != '' && $cashinfo->bank_account = $bank_account;
            $alipay_account != '' && $cashinfo->alipay_account = $alipay_account;
            $alipay_collect != '' && $cashinfo->alipay_collect = $alipay_collect;
            $wechat_account != '' && $cashinfo->wechat_account = $wechat_account;
            $wechat_nickname != '' && $cashinfo->wechat_nickname = $wechat_nickname;
            $wechat_collect != '' && $cashinfo->wechat_collect = $wechat_collect;
            $cashinfo->save();

            // 银行信息 20221126
            $bankcard = Bankcard::unguarded(function () use ($id) {
                return Bankcard::firstOrNew(['uid' => $id]);
            });
            $bank_name != '' && $bankcard->bankname = $bank_name;
            $bank_account != '' && $bankcard->bankcardno = $bank_account;
            $truename != '' && $bankcard->truename = $truename;
            $provincecity != '' && $bankcard->provincecity = $provincecity;
            $store != '' && $bankcard->store = $store;
            $idcard  != '' && $bankcard->idcard = $idcard;
            $international_code != '' && $bankcard->international_code = $international_code;
            $link_mp != '' && $bankcard->link_mp = $link_mp;
            $bankcard->save();

            // 实名信息
            if($name || $card_id) {
                $real = UserReal::unguarded(function () use ($id) {
                    return UserReal::firstOrNew(['user_id' => $id], ['review_status' => 1]);
                });
                $name != '' && $real->name = $name;
                $card_id != '' && $real->card_id = $card_id;
                $real->save();
            }
            
            
            DB::commit();
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function del(Request $request)
    {
        return $this->error('禁止删除用户,将会造成系统崩溃');
        $id = $request->input('id');
        $user = Users::getById($id);
      
        if (empty($user)) {
            $this->error("用户未找到");
        }
        try {
            $user->delete();
            AccountLog::where('user_id',$id)->delete();
            Address::where('user_id',$id)->delete();
            BlindBoxOrder::where('user_id',$id)->delete();
            LeverTransaction::where('user_id',$id)->delete();
            LockMiningOrder::where('user_id',$id)->delete();
            MicroOrder::where('user_id',$id)->delete();
            RechargeRecord::where('user_id',$id)->delete();
            UserReal::where('user_id',$id)->delete();
            UsersWallet::where('user_id',$id)->delete();
            WalletLog::where('user_id',$id)->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function lock(Request $request)
    {
        $id = $request->input('id', 0);

        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        if ($user->status == 1) {
            $user->status = 0;
        } else {
            $user->status = 1;
        }
        try {
            $user->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    
    public function googleCancel(Request $request) {
         $id = $request->input('id', 0);
        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        $user->google_secret = null;
        try {
            $user->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function allowExchange(Request $request)
    {
        $id = $request->input('id', 0);
        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        try {
            $user->type = 1 - $user->type;
            $user->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function wallet(Request $request)
    {
        $id = $request->input('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $currencies = Currency::where('parent_id', 0)->orderBy('id','asc')->get();
         $authorityList=session()->get("authorityList");
        return view("admin.user.user_wallet", [
            'user_id' => $id,
            'currencies' => $currencies,
            'authorityList' =>$authorityList
            
        ]);
    }

    public function walletList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = $request->get('user_id', null);
        $currency_id = $request->input('currency_id', 0);
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        //$currency_quotation = CurrencyQuotation::where('match_id', $match_id)->first();
      
        // exit;
        $list = UsersWallet::where('user_id', $user_id)
            ->when($currency_id > 0, function ($query) use ($currency_id) {
                $query->where('currency', $currency_id);
            })
            // ->whereHas('currencyCoin', function ($query) {
            //     $query->where('parent_id', 0); // 不显示子协议钱包
            // })
            // ->orderBy('id', 'asc')
            ->orderByRaw("field(currency, " . implode(", ", [32, 35, 23]) . ") desc")
            
            ->paginate($limit);
            // $market = MarketHour::getLastEsearchMarket('BTC','USDT', '1min');
            //     var_dump($market[$key]['close']);    
            
        $list->transform(function ($value,$key) {
                // $coin = $value['currency_name'];
             
                
                // $usdt_price = $market[$key]['close'];
                $value['total_change_balance'] = ($value['change_balance']+$value['lock_change_balance'])*$value['usdt_price'];
                $value['total_legal_balance'] = ($value['legal_balance']+$value['lock_legal_balance'])*$value['usdt_price'];
                $value['total_lever_balance'] = ($value['lever_balance']+$value['lock_lever_balance'])*$value['usdt_price'];
                $value['total_micro_balance'] = ($value['micro_balance']+$value['lock_micro_balance'])*$value['usdt_price'];
           
                
                return $value;
        });
       

//             ->each(function($item, $key){
//                 // $item['currency_name'];
// //  8         $wctypeid = $item["id"]; //获取数据集中的id
// //  9         $num = Db::name('wcmall_type_attribute')->where("wctypeid='$wctypeid'")->count('id'); //根据ID查询相关其他信息
// // 10         $item['num'] = $num; //给数据集追加字段num并赋值
//          return $item;
//         });
            
        return  $this->layuiData($list);
    }
    
    public function batchRisk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $risk = $request->input('risk', 0);
            if (empty($ids)) {
                throw new \Exception('请先选择用户');
            }
            if (!in_array($risk, [-1, 0, 1])) {
                throw new \Exception('风控类型不正确');
            }
            $affect_rows = Users::whereIn('id', $ids)
                ->update([
                    'risk' => $risk,
                ]);
            return $this->success('本次提交:' . count($ids) . '条,设置成功:' . $affect_rows . '条');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
    
    //钱包锁定状态
    public function walletLock(Request $request)
    {
        $id = $request->input('id', 0);

        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            return $this->error('参数错误');
        }
        if ($wallet->status == 1) {
            $wallet->status = 0;
        } else {
            $wallet->status = 1;
        }
        try {
            $wallet->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /*
     * 调节账户
     * */
    public function conf(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $result = UsersWallet::find($id);
        if (empty($result)) {
            return $this->error('无此结果');
        }
        $account = Users::where('id', $result->user_id)->value('phone');
        if (empty($account)) {
            $account = Users::where('id', $result->user_id)->value('email');
        }
        $result['account'] = $account;
        
        return view('admin.user.conf', ['results' => $result]);
    }

    //调节账号  type  1法币交易余额  2法币交易锁定余额 3币币交易余额 4币币交易锁定余额  5杠杆交易余额 6杠杆交易锁定余额
    public function postConf(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'way' => 'required|string', //增加 increment；减少 decrement
                //'type' => 'required|integer|min:1',
                //'conf_value' => 'required|numeric|min:0', //值
                'info' => 'required'
            ], [
                'required' => ':attribute 不能为空',
            ], [
                'info' => '调节备注'
            ]);

            $wallet = UsersWallet::find($request->input('id'));
            $user = Users::getById($wallet->user_id);

            //以上验证通过后 继续验证
            $validator->after(function ($validator) use ($wallet, $user) {
                if (empty($wallet)) {
                    return $validator->errors()->add('wallet', '没有此钱包');
                }

                if (empty($user)) {
                    return $validator->errors()->add('user', '没有此用户');
                }
            });

            //如果验证不通过
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }
            

            $way = $request->input('way', 'increment');
            $type = $request->input('type', 1);
            $conf_value = $request->input('conf_value', 0);
            $info = $request->input('info', ':');
            
            $balance_type = ceil($type / 2);
            $is_lock = false;
            $scene_list = [
                1 => AccountLog::ADMIN_LEGAL_BALANCE,
                2 => AccountLog::ADMIN_LOCK_LEGAL_BALANCE,
                3 => AccountLog::ADMIN_CHANGE_BALANCE,
                4 => AccountLog::ADMIN_LOCK_CHANGE_BALANCE,
                5 => AccountLog::ADMIN_LEVER_BALANCE,
                6 => AccountLog::ADMIN_LOCK_LEVER_BALANCE,
                7 => AccountLog::ADMIN_SECOND_LEVER_BALANCE,
                8 => AccountLog::ADMIN_LOCK_SECOND_LEVER_BALANCE
            ];
            // $en_info = "System quota adjustment";
            $en_info = $info;
            
            $way == 'decrement' &&  $conf_value = -$conf_value;
            
            //$result = change_wallet_balance($wallet, $type, $conf_value, $scene_list[$type], $info,$en_info, $is_lock);
            //
            
              $result=null;
           
            if ("0"==$type){
                $type=0;
                $result = change_wallet_balance($wallet, 0, $conf_value, $scene_list[1], $info,$en_info, false);
            }
            if ("10"==$type||$type==10){
                $result = change_wallet_lock_balance($wallet, 0, $conf_value, $scene_list[2], $info,$en_info, true);
            }
            //
            if ("1"==$type){
                $type=1;
                $result = change_wallet_balance($wallet, 1, $conf_value, $scene_list[3], $info,$en_info, false);
            }
            if ("11"==$type){
                $type=1;
                $result = change_wallet_lock_balance($wallet, 1, $conf_value, $scene_list[4], $info,$en_info, true);
            }
            
            //
             if ("2"==$type){
                $type=2;
                $result = change_wallet_balance($wallet, 2, $conf_value, $scene_list[5], $info,$en_info, false);
            }
            if ("12"==$type){
                $type=2;
                $result = change_wallet_lock_balance($wallet, 2, $conf_value, $scene_list[6], $info,$en_info, true);
            }
           
            //
            if ("3"==$type){
                $type=3;
                $result = change_wallet_balance($wallet, 3, $conf_value, $scene_list[7], $info,$en_info, false);
            }
            if ("13"==$type){
                $type=3;
                $result = change_wallet_lock_balance($wallet, 3, $conf_value, $scene_list[8], $info,$en_info, true);
            }
            
            //
            if ("14"==$type){
                $type=4;
                $result = change_wallet_lock_balance($wallet, 4, $conf_value, $scene_list[1], $info,$en_info, $is_lock);
            }
            if ("4"==$type){
                $type=4;
                $result = change_wallet_balance($wallet, 4, $conf_value, $scene_list[1], $info,$en_info, $is_lock);
            }
            
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function delWithdrawAddress01(Request $request){
        $id = $request->input('id');
        $po=PaymentMethod::find($id);
        $po->delete();
        return $this->success('删除成功');
    }

    //删除钱包
    public function delw(Request $request)
    {
        $id = $request->input('id');
        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            $this->error("钱包未找到");
        }
        try {
            $wallet->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
    
    public function editAddress(Request $request) {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
         $result = PaymentMethod::find($id);
        if (empty($result)) {
            return $this->error('无此结果');
        }
         return view('admin.user.edit_address', ['result' => $result]);
    }
    public function doEditAddress(Request $request) {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $data = PaymentMethod::find($id);
        if (empty($data)) {
            return $this->error('无此结果');
        }
        $data->coin = $request->input('coin','');
        $data->address = $request->input('address','');
        $data->upload_pic = $request->input('upload_pic','');
        $result = $data->save();
        if($result) {
            return $this->success('修改提币地址成功');
        }
         return $this->error("修改提币地址失败");
    }

    /*
     * 提币地址信息
     * */
    public function address(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $result = UsersWallet::find($id);
        if (empty($result)) {
            return $this->error('无此结果');
        }
        $currency_name=$request->input('currency_name', '');


        $list = PaymentMethod::where('uid', $result->user_id)->where('coin', $currency_name)->get();
        if (!empty($list)){
            foreach ($list as $po){
                $coin=$po->coin;
                if ($coin=="USDT"){
                    $lianType=$po->lianType;
                    if ($lianType==0){
                        $po->lianTypeZH="ERC20";
                    }
                    if ($lianType==1){
                        $po->lianTypeZH="TRC20";
                    }
                    if ($lianType==2){
                        $po->lianTypeZH="OMNI";
                    }
                }else{
                    $po->lianTypeZH="";
                }

            }
        }
        return view('admin.user.address', ['results' => $result, 'list' => $list]);
    }
    /*
     * 修改提币地址信息
     * */
    public function addressEdit(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        $currency = $request->input('currency', '');
        $total_arr = $request->input('total_arr', '');
        if (empty($user_id) || empty($currency)) {
            return $this->error('参数错误');
        }
        DB::beginTransaction();
        try {
            PaymentMethod::where('uid', $user_id)->where('coin', $currency)->delete();
            if (!empty($total_arr)) {
                foreach ($total_arr as $key => $val) {
                    $ads = new PaymentMethod();
                    $ads->uid = $user_id;
                    $ads->coin = $currency;
                    $ads->address = $val['address'];
                    $ads->notes = $val['notes'];
                    $ads->save();
                }
            }
            DB::commit();
            return $this->success('修改提币地址成功');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error($e->getMessage());
        }
    }

    //加入黑名单
    public function blacklist(Request $request)
    {
        $id = $request->input('id', 0);

        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        if ($user->is_blacklist == 1) {
            $user->is_blacklist = 0;
        } else {
            $user->is_blacklist = 1;
        }
        try {
            $user->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function candyConf(Request $request, $id)
    {
        $user = Users::find($id);
        return view('admin.user.candy_conf')->with('user', $user);
    }

    public function postCandyConf(Request $request, $id)
    {
        $user = Users::find($id);
        $way = $request->input('way', 0);
        $change = $request->input('change', 0);
        $memo = $request->input('memo', '');
        if (!in_array($way, [1, 2])) {
            return $this->error('调整方式传参错误');
        }
        if ($change <= 0) {
            return $this->error('调整金额必须大于0');
        }
        if ($way == 2) {
            $change = bc_mul($change, -1);
        }
        $result = change_user_candy($user, $change, AccountLog::ADMIN_CANDY_BALANCE, '后台调整' . ($way == 2 ? '减少' : '增加') . '通证 ' . $memo);
        return $result === true ? $this->success('调整成功') : $this->error('调整失败:' . $result);
    }
    
    // 发送站内信
    public function sendMailIndex(Request $request){
        $id = $request->input('id', 0);
        $result=Users::getById($id);
        return view("admin.user.sendMail",[ 'result' => $result]);
    }

    public function sendMail(Request $request){
        $user_id = $request->input('user_id', 0);
        $title = $request->input('title', 0);
        $content = $request->input('content', 0);
        
        $result = new Mail();
        $result->user_id = $user_id;
        $result->title = $title;
        $result->content = $content;
        $result->save();
        return $this->success('发送成功');
    }
}
