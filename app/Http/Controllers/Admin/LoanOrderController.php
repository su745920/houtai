<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Models\{LoanSetting, LoanOrder,AccountLog,UsersWallet,Users};
use DB;
use Validator;

class LoanOrderController extends Controller
{
    use ValidatesRequests;


    public function index()
    {
        return view("admin.loan.index");
    }

    public function lists()
    {
        $limit = request()->input('limit', 10);
        $account_number = request()->input('account_number', ''); //用户交易账号
        $user_id = request()->input('user_id', '');
        $order_no = request()->input('order_no', '');
        $result = new LoanOrder();
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
    * 贷款通过
    *
    * @return 
    */
    public function postPass(Request $request)
    {
        $id = $request->input('id',0);
        try {
             DB::beginTransaction();
            $loanOrder = LoanOrder::find($id);
            $loanOrder->status = 1;
            $loanOrder->save();
            // 申请贷款批准之后，贷款输入的金额就会自动到客户的资金账户
             $user_wallet = UsersWallet::where('user_id', $loanOrder->user_id)
                    ->where('currency', 23)
                    ->firstOrFail();
             change_wallet_balance(
                $user_wallet,
                0,
                $loanOrder->loan_money,
                AccountLog::LOAN_PASS,
                "贷款订单: 贷款审核通过,订单号:{$loanOrder->order_no},贷款金额:{$loanOrder->loan_money}",
                "Loan order: approved loan review, order number:{$loanOrder->order_no}",
                false, 
                 0, 
                 0, 
                 '', 
                 false, 
                 false, 
                 false,
                 $id
            );
            DB::commit();
            return $this->success('通过成功！');
        } catch (\Exception $ex) {
             DB::rollBack();
             return $this->error($ex->getMessage());
        }
    }

    /**
    * 贷款拒绝
    *
    * @return 
    */

    public function postReject(Request $request)
    {
        $id = $request->input('id',0);
        $loanOrder = LoanOrder::find($id);
        // 状态修改为2
        $loanOrder->status = 2;
        $result = $loanOrder->save();
        return $result ? $this->success('操作成功！') : $this->error('操作失败！');
    }
    
    /**
    * 贷款重置
    *
    * @return 
    */
    public function postReset(Request $request)
    {
        $id = $request->input('id',0);
        $account_log =  AccountLog::where('recharge_out_record_id',$id)->first();
        if(empty($account_log)) {
            return $this->error("记录不存在");
        }
        
        
        try {
             DB::beginTransaction();
            $loanOrder = LoanOrder::find($id);
            $loanOrder->status = 0;
            $loanOrder->save();
            // 申请贷款批准之后，贷款输入的金额就会自动到客户的资金账户
             $user_wallet = UsersWallet::where('user_id', $loanOrder->user_id)
                    ->where('currency', 23)
                    ->lockForUpdate()
                    ->firstOrFail();
            // 要账户的USDT余额足够的情况下，才能重置，不然后台就提示资金不足
            if($user_wallet->legal_balance < $loanOrder->loan_money) {
                throw new \Exception("钱包资金不足");
            }
                    
             $result = reset_change_wallet_balance(
                $user_wallet,
                0,
                -$loanOrder->loan_money,
                $id);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('重置成功！');
        } catch (\Exception $ex) {
             DB::rollBack();
             return $this->error($ex->getMessage());
        }
    }

    /**
    * 后台贷款删除
    *
    * @return 
    */

    public function postDel(Request $request)
    {
        $id = $request->input('id',0);
        return $id;
        //需要先判断所属贷款下是否有待还金额
        // $count = LoanOrder::where('loan_setting_id',  $id)->count();
        // if($count > 0) {
        //     return $this->error('删除失败，原因：贷款设置下有贷款订单！');
        // } else {
        //     $result = LoanSetting::destroy($id);
        //     return $result ? $this->success('删除成功！') : $this->error('删除失败！');
        // }
    }
}
