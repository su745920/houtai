<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use App\Models\{AccountLog, Currency, Setting, Users, UsersWallet, RechargeRecord,WalletSetting,Agent};
use Illuminate\Support\Facades\DB;

class AccountLogController extends Controller
{
    
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
        return view("agent.account.index", [
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
        // 查询代理关联的
        $agent_id = Agent::getAgentId();
        $users = Users::where('agent_note_id',$agent_id)->get()->pluck('id');
        $list = $list->where(function ($query) use ($users) {
            $query->whereIn('user_id', $users);
        });
        
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
        return view('agent.account.viewDetail', ['results' => $results]);
    }
}
