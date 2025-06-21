<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use App\Models\MicroNumber;
use App\Models\MicroOrder;
use App\Models\MicroSecond;
use App\Models\LockMining;
use App\Models\LockMiningOrder;
use App\Models\Currency;
use App\Models\CurrencyMatch;
use App\Models\Setting;
use App\Models\UsersWallet;
use App\Models\Users;
use App\Models\Agent;
class LockController extends Controller
{

    public function order()
    {
        $currencies = Currency::where('is_display', 1)->get();
        $authorityList=session()->get("authorityList");
        return view('agent.lock.orders')
            ->with('currencies', $currencies)
            ->with('authorityList',$authorityList);
    }

    public function orderList(Request $request)
    {
        $from_name = $request->input('from_name', '');
        $to_name = $request->input('to_name', '');
        $account = $request->input('account', '');
        $name = $request->input('name', '');
        $limit = $request->input('limit', 10);
        $status = $request->input('status', '');
        $start = $request->input("start_time", '');
        $end = $request->input("end_time", '');
        $user_id = request()->input('user_id', '');
        
         // 查询代理关联的
        $agent_id = Agent::getAgentId();
        $users = Users::where('agent_note_id',$agent_id)->get()->pluck('id');
        
        $results = LockMiningOrder::with(['user'])
            ->where(function ($query) use ($users) {
                $query->whereIn('user_id', $users);
            })
            ->when($to_name!='', function ($query) use ($to_name) {
                $query->where('to_name', $to_name);
            })->when($from_name != '', function ($query) use ($from_name) {
                $query->where('from_name', $from_name);
            })->when($status != '', function ($query) use ($status) {
                $query->where('status', $status);
            })->when($account != '' || $name != '', function ($query) use ($account, $name) {
                $query->whereHas('user', function ($query) use ($account, $name) {
                    $account != '' && $query->where("phone", 'like', '%' . $account . '%')->orwhere('email', 'like', '%' . $account . '%');
                });
            })->when($start !='', function ($query) use ($start) {
                $query->where('created_at','>=', strtotime($start));
            })->when($end !='', function ($query) use ($end) {
                $query->where('created_at','<=', strtotime($end));
            })->orderBy('id', 'desc');
            
        if (!empty($user_id)) {
            $results = $results->where('user_id', '=', $user_id);
        }
        $results = $results->paginate($limit);
        return $this->layuiData($results);
    }
    
     public function del(Request $request)
    {
        $id = $request->get('id', 0);
        $lock_mining = LockMining::find($id);
        if (empty($lock_mining)) {
            return $this->error('参数错误');
        }
        try {
            $lock_mining->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}
