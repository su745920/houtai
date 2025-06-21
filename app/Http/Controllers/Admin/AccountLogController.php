<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{AccountLog, Currency, Setting, Users, UsersWallet, RechargeRecord,WalletSetting};
use Illuminate\Support\Facades\DB;

class AccountLogController extends Controller
{
    
    public function recharge_v2_fail()
    {
        $balance_from = Setting::getValueByKey('withdraw_from_balance', 1); // 从哪个账户提币(1.法币,2.币币,3.杠杆)
        $balance_type = [
            1 => ['legal', '法币', 'is_legal'],
            2 => ['change', '币币', 'is_match'],
            3 => ['lever', '杠杆币', 'is_lever'],
        ];
        $currencies = Currency::where($balance_type[$balance_from][2], 1)
            ->where('parent_id', 0)
            ->where('is_display', 1)
            //->where('is_recharge', 1)
            ->get();

        $authorityList=session()->get("authorityList");
        return view('admin.account.recharge_v2_fail')->with('currencies', $currencies)->with('authorityList',$authorityList);
    }


    public function recharge_v2_success()
    {
        $balance_from = Setting::getValueByKey('withdraw_from_balance', 1); // 从哪个账户提币(1.法币,2.币币,3.杠杆)
        $balance_type = [
            1 => ['legal', '法币', 'is_legal'],
            2 => ['change', '币币', 'is_match'],
            3 => ['lever', '杠杆币', 'is_lever'],
        ];
        $currencies = Currency::where($balance_type[$balance_from][2], 1)
            ->where('parent_id', 0)
            ->where('is_display', 1)
            //->where('is_recharge', 1)
            ->get();

        $authorityList=session()->get("authorityList");
        return view('admin.account.recharge_v2_success')->with('currencies', $currencies)->with('authorityList',$authorityList);
    }
    
    
    public function recharge_v2()
    {
        $balance_from = Setting::getValueByKey('withdraw_from_balance', 1); // 从哪个账户提币(1.法币,2.币币,3.杠杆)
        $balance_type = [
            1 => ['legal', '法币', 'is_legal'],
            2 => ['change', '币币', 'is_match'],
            3 => ['lever', '杠杆币', 'is_lever'],
        ];
        $currencies = Currency::where($balance_type[$balance_from][2], 1)
            ->where('parent_id', 0)
            ->where('is_display', 1)
            //->where('is_recharge', 1)
            ->get();

        $authorityList=session()->get("authorityList");
        return view('admin.account.recharge_v2')->with('currencies', $currencies)->with('authorityList',$authorityList);
    }
    public function index()
    {
        //获取type类型
        $type = [
            AccountLog::ADMIN_LEGAL_BALANCE => '后台调节法币账户余额',
            AccountLog::ADMIN_LOCK_LEGAL_BALANCE => '后台调节法币账户锁定余额',
            AccountLog::ADMIN_CHANGE_BALANCE => '后台调节币币账户余额',
            AccountLog::ADMIN_LOCK_CHANGE_BALANCE => '后台调节币币账户锁定余额',
            AccountLog::ADMIN_LEVER_BALANCE => '后台调节杠杆账户余额',
            AccountLog::ADMIN_LOCK_LEVER_BALANCE => '后台调节杠杆账户锁定余额',
            AccountLog::WALLET_CURRENCY_OUT => '法币账户转出至交易账户',
            AccountLog::WALLET_CURRENCY_IN => '交易账户转入至法币账户',
            AccountLog::TRANSACTIONOUT_SUBMIT_REDUCE => '提交卖出，扣除',
            AccountLog::TRANSACTIONIN_REDUCE => '币币交易:买入扣除',
            AccountLog::TRANSACTIONIN_OUT_DEL => '币币交易:挂卖撤单',
            AccountLog::TRANSACTIONIN_IN_DEL => '币币交易:挂买撤单',
        ];
        $currency_type = Currency::where('parent_id', 0)->get();
        $authorityList=session()->get("authorityList");
        return view("admin.account.index", [
            'types' => $type,
            'currency_type' => $currency_type,
            'authorityList' =>$authorityList
        ]);
    }

    public function lists(Request $request)
    {
        $limit = $request->input('limit', 10);
        $id = $request->input('id', '');
        $user_id = $request->input('user_id', '');
        $account = $request->input('account', '');
        $start_time = strtotime($request->input('start_time', 0));
        $end_time = strtotime($request->input('end_time', 0));
        $currency = $request->input('currency_type', 0);
        $type = $request->input('type', 0);
        $balance_type = $request->input('balance_type', 0);
        $lock_type = $request->input('lock_type', -1);

        $list = AccountLog::query();
        $list = $list->with(['user', 'walletLog']);
        if (!empty($id)) {
            $list = $list->where('id', $id);
        }
        if (!empty($user_id)) {
            $list = $list->where('user_id', $user_id);
        }
        if (!empty($currency)) {
            $list = $list->where('currency', $currency);
        }
        if (!empty($type)) {
            $list = $list->where('type', $type);
        }
        if (!empty($start_time)) {
            $list = $list->where('created_time', '>=', $start_time);
        }
        if (!empty($end_time)) {
            $list = $list->where('created_time', '<=', $end_time);
        }
        if (!empty($account)) {
            // $user = Users::where("phone", 'like', $account . '%')->orWhere('email', $account . '%')->first();
            $user = Users::Where('email', $account)->first();
        //   var_dump($user);
            
            $list = $list->where(function ($query) use ($user) {
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            });
        }

        if (!empty($balance_type)) {
            $list = $list->whereHas('walletLog', function ($query) use ($balance_type) {
                $query->where('balance_type', $balance_type);
            });
        }

        if ($lock_type > -1) {
            $list = $list->whereHas('walletLog', function ($query) use ($lock_type) {
                $query->where('lock_type', $lock_type);
            });
        }

        $list = $list->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($list);
    }

    public function view(Request $request)
    {
        $id = $request->get('id', null);
        $results = new AccountLog();
        $results = $results->where('id', $id)->first();
        if (empty($results)) {
            return $this->error('无此记录');
        }
        return view('admin.account.viewDetail', ['results' => $results]);
    }

    public function recharge()
    {
        $balance_from = Setting::getValueByKey('withdraw_from_balance', 1); // 从哪个账户提币(1.法币,2.币币,3.杠杆)
        $balance_type = [
            1 => ['legal', '法币', 'is_legal'],
            2 => ['change', '币币', 'is_match'],
            3 => ['lever', '杠杆币', 'is_lever'],
        ];
        $currencies = Currency::where($balance_type[$balance_from][2], 1)
            ->where('parent_id', 0)
            ->where('is_display', 1)
            //->where('is_recharge', 1)
            ->get();
        return view('admin.account.recharge')->with('currencies', $currencies);
    }

    public function rechargeList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $lists = AccountLog::where(function ($query) {
                $query->where('type', AccountLog::CHAIN_RECHARGE)->where('user_id', '>', 0);
            })->whereHas('user', function ($query) use ($request) {
                $account_number = $request->input('account_number', '');
                $account_number != '' && $query->where('account_number', $account_number);
                $user_id = $request->input('user_id', 0);
                $user_id != '' && $query->where('user_id',  $user_id );
            })->where(function ($query) use ($request) {
                $currency = $request->input('currency', -1);
                $start_time = strtotime($request->input('start_time', null));
                $end_time = strtotime($request->input('end_time', null));
                $currency != -1 && $query->where('currency', $currency);
                $start_time && $query->where('created_time', '>=', $start_time);
                $end_time && $query->where('created_time', '<=', $end_time);
            })->orderBy('id', 'desc')
            ->paginate($limit);
        $sum = $lists->sum('value');
        return $this->layuiData($lists, $sum);
    }

    public function indexprofits()
    {
        $scene_list = AccountLog::where("type", AccountLog::PROFIT_LOSS_RELEASE)
            ->orderBy("created_time", "desc")
            ->get()
            ->toArray();
        return view('admin.profits.index')->with('scene_list', $scene_list);
    }

    public function listsprofits(Request $request)
    {
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
        })->where("type", AccountLog::PROFIT_LOSS_RELEASE)->orderBy('id', 'desc')->paginate($limit);

        return $this->layuiData($prize_pool);
    }

    public function countprofits(Request $request)
    {
        $count_data = AccountLog::selectRaw('1 as user_count')
            ->selectRaw('sum(`value`) as value')
            ->whereHas('user', function ($query) use ($request) {
                $account_number = $request->input('account_number');
                if ($account_number) {
                    $query->where('account_number', $account_number)
                        ->orWhere('phone', $account_number)
                        ->orWhere('email', $account_number);
                }
            })->where(function ($query) use ($request) {
                $start_time = strtotime($request->input('start_time', null));
                $end_time = strtotime($request->input('end_time', null));
                $start_time && $query->where('created_time', '>=', $start_time);
                $end_time && $query->where('created_time', '<=', $end_time);
            })->where("type", AccountLog::PROFIT_LOSS_RELEASE)->groupBy('user_id')->get();
        $user_count = $count_data->pluck('user_count')->sum();
        $reward_total = 0;
        $count_data->pluck('value')->each(function ($item, $key) use (&$reward_total) {
            $reward_total = bc_add($reward_total, $item);
        });
        return response()->json([
            'user_count' => $user_count,
            'reward_total' => $reward_total,
        ]);
    }
    
    public function record()
    {
        $balance_from = Setting::getValueByKey('withdraw_from_balance', 1); // 从哪个账户提币(1.法币,2.币币,3.杠杆)
        $balance_type = [
            1 => ['legal', '法币', 'is_legal'],
            2 => ['change', '币币', 'is_match'],
            3 => ['lever', '杠杆币', 'is_lever'],
        ];
        $currencies = Currency::where($balance_type[$balance_from][2], 1)
            ->where('parent_id', 0)
            ->where('is_display', 1)
            ->get();
            
        return view('admin.account.record')->with('currencies', $currencies);
    }
    
    public function recordList(Request $request){
        $limit = $request->input('limit', 10);
        $account_number = $request->input('account_number', '');
        $created_at = $request->input('created_at', '');
        $updated_at = $request->input('updated_at', '');
        $status = $request->input('status','');
        $lists = RechargeRecord::query();
        
        
        if (!empty($account_number)) {
            $lists = $lists->where('phone', $account_number);
        }
        if (!empty($status)) {
            $lists = $lists->where('state', $status);
        }
        if (!empty($created_at)) {
            $lists = $lists->where('created_at', '>=', $created_at);
        }
        if (!empty($updated_at)) {
            $lists = $lists->where('updated_at', '<=', $updated_at);
        }
        // if (!empty($account)) {
        //     $user = Users::where("phone", 'like', '%' . $account . '%')->orWhere('email', '%' . $account . '%')->first();
        //     $list = $list->where(function ($query) use ($user) {
        //         if ($user) {
        //             $query->where('user_id', $user->id);
        //         }
        //     });
        // }
     
     
     
     
     
     
     
        if (!empty($account_number)) {
            $lists = $lists->where('phone', $account_number);
        }
        // if (!empty($start_time)) {
        //     $lists = $lists->where('created_time', '>=', $start_time);
        // }
        $lists = $lists->orderBy('id','desc')->paginate($limit);

        $eftBankName= "";
        $res = WalletSetting::where('wallet_type','eft')
                ->where('parameter','bankname')
                ->first();
        if($res){
            $eftBankName = $res->value;
        }
        $eftAccountNo= "";
        $res = WalletSetting::where('wallet_type','eft')
                ->where('parameter','account_no')
                ->first();
        if($res){
            $eftAccountNo = $res->value;
        }
        $chinaEftBankName = "";
        $res = WalletSetting::where('wallet_type','china_eft')
                ->where('parameter','bankname')
                ->first();
        if($res){
            $chinaEftBankName = $res->value;
        }

        $chinaEftAccountNo= "";
        $res = WalletSetting::where('wallet_type','china_eft')
                ->where('parameter','account_no')
                ->first();
        if($res){
            $chinaEftAccountNo = $res->value;
        }

        $lists->getCollection()->transform(function ($model) {
            $model->currency_id = $model->currency;
            $model->currency = Currency::where('id',$model->currency)->value('name');
            $model->twd = $model->twd * $model->money;
            return $model;
        });
        foreach($lists as &$k){
            if($k->currency_id == 58){
                $k->address = $eftBankName.$eftAccountNo;      
            }
            if($k->currency_id == 581){
                $k->address = $chinaEftBankName.$chinaEftAccountNo;
            }
        }
        /*foreach($result['data'] as &$k){
            $k['currency'] = Currency::where('id',$k['currency'])->value('name');
            $k['twd'] = $k['twd'] * $k['money'];
        }*/
        return $this->layuiData($lists);
    }
    
    public function change_state(Request $request){
        $id = $request->input('id','');
        $state = $request->input('state',1);
        if(!in_array($state,[2,3])){
            return $this->error('类型错误');
        }
        $recharge_record = DB::table('recharge_record')->where('id',$id)->first();
        $recharge_record = json_decode(json_encode($recharge_record), true);
        $res = DB::table('recharge_record')->where('id',$id)->update(['state' => $state]);
        if($res && $state == 2){
            //加到资产明细里面
            $type = 3;
            $balance_type = ceil($type / 2);
            $is_lock = $type % 2 ? false : true;
            $scene_list = [
                1 => AccountLog::ADMIN_LEGAL_BALANCE,
                2 => AccountLog::ADMIN_LOCK_LEGAL_BALANCE,
                3 => AccountLog::ADMIN_CHANGE_BALANCE,
                4 => AccountLog::ADMIN_LOCK_CHANGE_BALANCE,
                5 => AccountLog::ADMIN_LEVER_BALANCE,
                6 => AccountLog::ADMIN_LOCK_LEVER_BALANCE,
                7 => 253,
                8 => 254
            ];
	        if($recharge_record['currency']==58) $recharge_record['currency']=23;
            $conf_value = $recharge_record['money'];
            $en_info = "System quota adjustment";
            $way = 'increment';
            $wallet = UsersWallet::where('user_id',$recharge_record['user_id'])->where('currency',$recharge_record['currency'])->first();
            $info = "后台审核充值";

            $user_id = $recharge_record['user_id'];
            $amount = $conf_value * $recharge_record['twd'];
            $sql='update users_wallet set cz_amount = cz_amount+'.$amount.' where user_id = '.$user_id.' and currency='.$recharge_record['currency'];
            DB::update($sql);
           
            $result = change_wallet_balance($wallet, $balance_type, $conf_value, $scene_list[$type], $info,$en_info, $is_lock);
            if ($result !== true) {
                throw new \Exception($result);
            }else{
                 return $this->success('通过成功');
            }
            
           
        }elseif ($res && $state == 3) {
            return $this->success('已拒绝');
        }else{
            return $this->error('失败');
        }
    }
}
