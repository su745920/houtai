<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Models\LoanSetting;
use App\Models\RepaymentRecord;
use App\Models\LoanOrder;
use App\Models\Agent;
use App\Models\Users;
use DB;
use Validator;

class RepaymentRecordController extends Controller
{
    use ValidatesRequests;

    public function index()
    {
        return view("agent.loan.repayment_record");
    }

    public function lists()
    {
        $limit = request()->input('limit', 10);
        $account_number = request()->input('account_number', ''); //用户交易账号
        $user_id = request()->input('user_id', '');
        $order_no = request()->input('order_no', '');
        $result = new RepaymentRecord();
        // 查询代理关联的
        $agent_id = Agent::getAgentId();
        $users = Users::where('agent_note_id',$agent_id)->get()->pluck('id');
        $result = $result->where(function ($query) use ($users) {
            $query->whereIn('user_id', $users);
        });
        
        if (!empty($account_number)) {
            $users = Users::where('account_number', 'like', '%' . $account_number . '%')->get()->pluck('id');
            $result = $result->where(function ($query) use ($users) {
                $query->whereIn('user_id', $users);
            });
        }
        if (!empty($order_no)) {
            $result = $result->where('order_no', 'like', '%' . $order_no . '%');
        }

        if (!empty($user_id)) {
            $result = $result->where('user_id', '=', $user_id);
        }

        $list = $result->orderBy('id', 'desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

    /**
     * @c_id integer 还款列表
     * @keyword string 关键词
     * @num integer 每页记录数，为0则不分页
     */

    public static function repaymentRecordList($order_no = '', $user_id = 0,$loan_setting_id = 0,  $num = 0)
    {
        $order_no = '%' . $order_no . '%';
        $loan_order_query = RepaymentRecord::where(function ($query) use ($order_no,$user_id,$loan_setting_id) {
                !empty($order_no) && $query->where('order_no', 'like', $order_no);
                $user_id > 0 && $query->where('user_id', $user_id);
                $loan_setting_id > 0 && $query->where('loan_setting_id', $loan_setting_id);
            })->orderBy('id', 'desc');
        $loan_order = $num != 0 ? $loan_order_query->paginate($num) : $loan_order_query->get();
        return $loan_order;
    }
    
    /**
    * 还款通过
    *
    * @return 
    */
    public function postPass(Request $request)
    {
        $id = $request->input('id',0);
        
         try {
            DB::beginTransaction();
            $repaymentRecord = RepaymentRecord::find($id);
            
            $loanOrder = LoanOrder::find($repaymentRecord->loan_order_id);
            $already_money = bcadd($loanOrder->already_money,$repaymentRecord->repayment_amount,6);
            $loanOrder->already_money = $already_money;
            // 判断是否全部还清 贷款金额 + 贷款利息 + 手续费
            $total_money = $loanOrder->loan_money + $loanOrder->interest + $loanOrder->commission;
            $status = 3;
            if($already_money >= $total_money) {
                $status = 6;
            }
            $loanOrder->status = $status;
            $loanOrder->save();
            
            // 状态修改为1
            $repaymentRecord->status = 1;
            $result = $repaymentRecord->save();
             if (!$result) {
                throw new \Exception('操作失败!');
            }
            DB::commit();
            return $this->success('操作成功！');
        } catch (\Exception $ex ) {
             DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    /**
    * 还款拒绝
    *
    * @return 
    */

    public function postReject(Request $request)
    {
        $id = $request->input('id',0);
        $repaymentRecord = RepaymentRecord::find($id);
        // 状态修改为2
        $repaymentRecord->status = 2;
        $result = $repaymentRecord->save();
        $loanOrder = LoanOrder::find($repaymentRecord->loan_order_id);
        $loanOrder->status = 5;
        $loanOrder->save();
        return $result ? $this->success('操作成功！') : $this->error('操作失败！');
    }

    /**
    * 后台还款删除
    *
    * @return 
    */

    public function postDel(Request $request)
    {
        $id = $request->input('id',0);
        return $id;
        //需要先判断贷款是否还清
        // $count = RepaymentRecord::where('loan_setting_id',  $id)->count();
        // if($count > 0) {
        //     return $this->error('删除失败，原因：贷款设置下有贷款订单！');
        // } else {
        //     $result = LoanSetting::destroy($id);
        //     return $result ? $this->success('删除成功！') : $this->error('删除失败！');
        // }
    }
}
