<?php

/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2018/12/4
 * Time: 16:36
 */

namespace App\Http\Controllers\Agent;
use App\Exports\FromQueryExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{
    AccountLog,
    UsersWalletOut,
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
    CurrencyQuotation
};

class UserController extends Controller
{

    //用户管理
    public function index()
    {
        //某代理商下用户时
        $parent_id = request()->get('parent_id', 0);
        $agent_id = Agent::getAgentId();
        if(empty($parent_id)) {
            $parent_id = $agent_id;
        }
        //法币  
        $legal_currencies = Currency::where('is_legal', 1)->get();
        return view("agent.user.index", ['parent_id' => $parent_id, 'legal_currencies' => $legal_currencies]);
    }

    //用户列表
    public function lists(Request $request)
    {
        $limit = $request->input('limit', 10);
        $id = request()->input('id', 0);
        $parent_id = request()->input('parent_id', 0);
        $account_number = request()->input('account_number', '');
        $start = request()->input('start', '');
        $end = request()->input('end', '');
        
        

        $users = new Users();

        $users = $users->leftjoin("user_real", "users.id", "=", "user_real.user_id");
        
        $users = $users->groupBy('users.id'); // 根据需要分组
        
        
        if ($id) {
            $users = $users->where('users.id', $id);
        }
        if ($parent_id > 0) {
            $users = $users->where('users.agent_note_id', $parent_id);
        }
        if ($account_number) {
            $users = $users->where('users.account_number', $account_number);
        }
        if (!empty($start) && !empty($end)) {
            $users->whereBetween('users.time', [strtotime($start . ' 0:0:0'), strtotime($end . ' 23:59:59')]);
        }
        //获取下级代理？？？
        // $my_agent_list=Agent::getLevel4AgentId(Agent::getAgentId(),[Agent::getAgentId()]);

        // $users = $users->whereIn('users.agent_note_id', $my_agent_list);

        $agent_id = Agent::getAgentId();
        // $users = $users->whereRaw("FIND_IN_SET($agent_id,users.agent_path)");
        

        $list = $users->select("users.*", "user_real.card_id")->paginate($limit);
        
         

        return $this->layuiData($list);
    }

    /**
     * 获取用户管理的统计
     * @param Request $r
     */
    public function get_user_num(Request $request)
    {

        $id             = request()->input('id', 0);
        $account_number = request()->input('account_number', '');
        $parent_id            = request()->input('parent_id', 0);//代理商id
        $start = request()->input('start', '');
        $end = request()->input('end', '');
        $currency_id = request()->input('currency_id', '');

        $users = new Users();

        if ($id) {
            $users = $users->where('id', $id);
        }
        if ($parent_id > 0) {
            $users = $users->where('agent_note_id', $parent_id);
        }
        if ($account_number) {
            $users = $users->where('account_number', $account_number);
        }
        if (!empty($start) && !empty($end)) {
            $users->whereBetween('time', [strtotime($start . ' 0:0:0'), strtotime($end . ' 23:59:59')]);
        }

        // $my_agent_list = Agent::getLevel4AgentId(Agent::getAgentId(),[Agent::getAgentId()]);

        // $users = $users->whereIn('agent_note_id', $my_agent_list);

        $agent_id = Agent::getAgentId();
        $users = $users->whereRaw("FIND_IN_SET($agent_id,`agent_path`)");
        $users_id = $users->get()->pluck('id')->all();
        $_daili = 0;
        $_ru = 0.00;
        $_chu = 0.00;
        $_num = 0;

        $_num = $users->count();

        $_daili = $users->where('agent_id', '>', 0)->count();


        $_ru = AccountLog::where('type', AccountLog::CHAIN_RECHARGE)
            ->whereIn('user_id', $users_id)
            ->when($currency_id > 0, function ($query) use ($currency_id) {
                $query->where('currency', $currency_id);
            })->sum('value');

        $_chu = UsersWalletOut::where('status', 2)
            ->whereIn('user_id', $users_id)
            ->when($currency_id > 0, function ($query) use ($currency_id) {
                $query->where('currency', $currency_id);
            })->sum('real_number');

        $data = [];
        $data['_num'] = $_num;
        $data['_daili'] = $_daili;
        $data['_ru'] = $_ru;
        $data['_chu'] = $_chu;


        return $this->ajaxReturn($data);
    }

    //我的邀请二维码
    public function get_my_invite_code()
    {

        $_self = Agent::getAgent();

        if ($_self == null) {
            $this->outmsg('超时');
        }

        $use = Users::getById($_self->user_id);

        return $this->ajaxReturn(['invite_code' => $use->extension_code, 'is_admin' => $_self->is_admin]);
    }

    //代理商管理
    public function salesmenIndex()
    {
        return view("agent.salesmen.index");
    }

    //添加代理商页面
    public function salesmenAdd()
    {
        $data = request()->all();

        return view("agent.salesmen.add", ['d' => $data]);
    }

    public function salesmenEdit()
    {
        $data = request()->all();
        return view("agent.salesmen.add", ['d' => $data]);
    }
    //出入金管理
    public function transferIndex()
    {
        return view("agent.user.transfer");
    }
    
    public function wallet(Request $request)
    {
        $id = $request->input('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $currencies = Currency::where('parent_id', 0)->orderBy('id','asc')->get();
         $authorityList=session()->get("authorityList");
        return view("agent.user.user_wallet", [
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
        $list = UsersWallet::where('user_id', $user_id)
            ->when($currency_id > 0, function ($query) use ($currency_id) {
                $query->where('currency', $currency_id);
            })
            ->orderByRaw("field(currency, " . implode(", ", [32, 35, 23]) . ") desc")
            
            ->paginate($limit);  
            
        $list->transform(function ($value,$key) {
                $value['total_change_balance'] = ($value['change_balance']+$value['lock_change_balance'])*$value['usdt_price'];
                $value['total_legal_balance'] = ($value['legal_balance']+$value['lock_legal_balance'])*$value['usdt_price'];
                $value['total_lever_balance'] = ($value['lever_balance']+$value['lock_lever_balance'])*$value['usdt_price'];
                $value['total_micro_balance'] = ($value['micro_balance']+$value['lock_micro_balance'])*$value['usdt_price'];
                return $value;
        });
        return  $this->layuiData($list);
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
        return view('agent.user.address', ['results' => $result, 'list' => $list]);
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
        
        return view('agent.user.conf', ['results' => $result]);
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
                //throw new \Exception($validator->errors()->first());
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
            $en_info = "System quota adjustment";
            
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
        
        return view('agent.user.edit', [
            'result' => $result,
            'cashinfo' => $cashinfo,
            'bankPO'=>$bankPO,
            'wallet_address_list' => $wallet_address_list,
            'agent_list' => $agent_list
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
    public function addressList(Request $request) {
        $limit = $request->get('limit', 10);
        $id = $request->input('id', 0);
        $list = PaymentMethod::where('uid', $id)
                ->orderBy('id', 'desc')
                ->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
    
    public function editFKRatio(Request $request){
        $id = $request->input('id', 0);
        $result=Users::getById($id);
        return view("agent.user.fkRatio",[ 'result' => $result]);
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
    
    // 发送站内信
    public function sendMailIndex(Request $request){
        $id = $request->input('id', 0);
        $result=Users::getById($id);
        return view("agent.user.sendMail",[ 'result' => $result]);
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
}
